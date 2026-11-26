<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\DoctorProfile;
use App\Models\Review;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentAvailabilityService $availability) {}

    private function notify(int $userId, string $title, string $message, string $type, array $data=[]): void
    {
        Notification::create(['user_id'=>$userId,'title'=>$title,'message'=>$message,'type'=>$type,'data'=>$data]);
    }

    public function store(Request $request): JsonResponse
    {
        $v=$request->validate([
            'doctor_profile_id'=>'required|integer|exists:doctor_profiles,id',
            'clinic_id'=>'required|integer|exists:clinics,id',
            'appointment_date'=>'required|date_format:Y-m-d',
            'start_time'=>'required|date_format:H:i',
            'reason'=>'nullable|string|max:1000','patient_phone'=>'required|string|max:30',
            'notes'=>'nullable|string|max:2000',
        ]);
        $patient=$request->user();
        $appointment=DB::transaction(function() use($v,$patient) {
            $doctorProfile=DoctorProfile::whereKey($v['doctor_profile_id'])->where('is_verified',true)
                ->whereHas('user',fn($q)=>$q->where('is_active',true))->lockForUpdate()->firstOrFail();
            abort_unless(\App\Models\Clinic::whereKey($v['clinic_id'])->where('is_active',true)->exists(),422,'Saýlanan klinika işjeň däl.');
            $schedule=DoctorSchedule::where('doctor_profile_id',$v['doctor_profile_id'])
                ->where('clinic_id',$v['clinic_id'])->where('day_of_week',Carbon::parse($v['appointment_date'], \App\Models\Clinic::whereKey($v['clinic_id'])->value('timezone') ?: config('app.timezone'))->dayOfWeekIso)
                ->where('is_active',true)->lockForUpdate()->first();
            if (!$schedule) throw ValidationException::withMessages(['start_time'=>['Lukman bu gün kabul etmeýär.']]);
            $duration=(int)$schedule->slot_duration_minutes;
            $start=Carbon::createFromFormat('H:i',$v['start_time']);
            $end=$start->copy()->addMinutes($duration);
            $this->availability->assertAvailable((int)$v['doctor_profile_id'],(int)$v['clinic_id'],$v['appointment_date'],$start->format('H:i:s'),$end->format('H:i:s'));
            $code='ST-'.date('Y').'-'.strtoupper(\Illuminate\Support\Str::random(8));
            $a=Appointment::create([
                'booking_code'=>$code,'patient_id'=>$patient->id,'doctor_profile_id'=>$v['doctor_profile_id'],
                'clinic_id'=>$v['clinic_id'],'created_by_user_id'=>$patient->id,
                'appointment_date'=>$v['appointment_date'],'start_time'=>$start->format('H:i:s'),
                'end_time'=>$end->format('H:i:s'),'duration_minutes'=>$duration,'status'=>'pending',
                'reason'=>$v['reason']??null,'patient_phone'=>$v['patient_phone'],'notes'=>$v['notes']??null
            ]);
            $doctor=DoctorProfile::with('user')->findOrFail($v['doctor_profile_id']);
            $this->notify($doctor->user_id,'Täze kabul ýazgysy','Täze näsag kabul üçin ýazyldy.','appointment_created',['appointment_id'=>$a->id]);
            $this->notify($patient->id,'Kabul ýazgysy döredildi','Kabul ýazgyňyz tassyklanmaga garaşýar.','appointment_created',['appointment_id'=>$a->id]);
            Payment::create(['appointment_id'=>$a->id,'amount'=>$doctor->consultation_fee,'currency'=>$doctor->currency ?: 'TMT','status'=>'pending','payment_method'=>'cash']);
            return $a;
        },3);
        return response()->json(['message'=>'Kabul ýazgysy üstünlikli döredildi.','data'=>$appointment],201);
    }

    public function myAppointments(Request $request): JsonResponse
    {
        return response()->json(Appointment::with(['doctor.user','doctor.specialization','clinic','payment','review'])
            ->where('patient_id',$request->user()->id)->latest('appointment_date')->latest('start_time')->paginate(15));
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $v=$request->validate(['reason'=>'nullable|string|max:500']);
        $a=DB::transaction(function() use($request,$id,$v) {
            $a=Appointment::with('clinic')->where('id',$id)->where('patient_id',$request->user()->id)->lockForUpdate()->firstOrFail();
            if (!in_array($a->status,['pending','confirmed'],true)) abort(422,'Bu kabul ýazgysyny ýatyryp bolmaýar.');
            $timezone = $a->clinic?->timezone ?: config('app.timezone', 'Asia/Ashgabat');
            $when=Carbon::parse($a->appointment_date->toDateString().' '.$a->start_time, $timezone);
            $hours=(int)(Setting::where('key','cancellation_hours')->value('value') ?? 12);
            if (now($timezone)->diffInMinutes($when,false)<($hours*60)) abort(422,'Kabul wagtyna '.$hours.' sagatdan az galanda özbaşdak ýatyryp bolmaýar.');
            $a->update(['status'=>'cancelled','cancelled_at'=>now(),'cancellation_reason'=>$v['reason']??'Näsag tarapyndan ýatyryldy']);
            $payment = Payment::where('appointment_id', $a->id)->lockForUpdate()->first();
            if ($payment && $payment->status === 'pending') {
                $payment->update(['status' => 'cancelled']);
                $payment->histories()->create([
                    'actor_user_id' => $request->user()->id,
                    'old_status' => 'pending',
                    'new_status' => 'cancelled',
                    'old_payment_method' => $payment->payment_method,
                    'new_payment_method' => $payment->payment_method,
                    'note' => 'Näsag bronuny ýatyrdy.',
                    'reason' => 'Näsag bronuny ýatyrdy.',
                    'amount' => $payment->amount,
                ]);
            }
            $doctor=$a->doctor()->with('user')->first();
            if($doctor) $this->notify($doctor->user_id,'Kabul ýatyryldy','Näsag kabul ýazgysyny ýatyrdy.','appointment_cancelled',['appointment_id'=>$a->id]);
            return $a;
        });
        return response()->json(['message'=>'Kabul ýazgysy ýatyryldy.','data'=>$a]);
    }

    public function storeReview(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'rating' => ['required', 'numeric', 'between:1,5', 'decimal:0,1'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment = Appointment::whereKey($id)
            ->where('patient_id', $request->user()->id)
            ->where('status', 'completed')
            ->firstOrFail();

        if (Review::where('appointment_id', $appointment->id)->exists()) {
            return response()->json(['message' => 'Bu kabul üçin baha öň berlipdir.'], 409);
        }

        $review = Review::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_profile_id' => $appointment->doctor_profile_id,
            'rating' => $v['rating'],
            'comment' => $v['comment'] ?? null,
        ]);

        return response()->json(['message' => 'Pikiriňiz kabul edildi.', 'data' => $review], 201);
    }


    public function updateReview(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'rating' => ['sometimes', 'required', 'numeric', 'between:1,5', 'decimal:0,1'],
            'comment' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);
        $review = Review::whereKey($id)->where('patient_id', $request->user()->id)->firstOrFail();
        abort_unless($review->appointment()->where('status','completed')->exists(), 422, 'Diňe tamamlanan kabul üçin baha üýtgedip bolýar.');
        $review->update($data);
        return response()->json(['message'=>'Bahalandyrma täzelendi.','data'=>$review->fresh()]);
    }

    public function deleteReview(Request $request, int $id): JsonResponse
    {
        $review = Review::whereKey($id)->where('patient_id', $request->user()->id)->firstOrFail();
        $review->delete();

        return response()->json(['message' => 'Bahalandyrmaňyz pozuldy.']);
    }

    public function doctorAppointmentShow(Request $request, int $id): JsonResponse
    {
        $profile = $request->user()->doctorProfile;
        abort_unless($profile, 404, 'Lukman profili tapylmady.');

        $appointment = Appointment::with([
            'patient:id,name,email,phone,avatar_path',
            'createdBy:id,name,role',
            'doctor.user:id,name',
            'clinic:id,name,address,phone',
            'payment.histories.actor:id,name,role',
            'review.patient:id,name,avatar_path',
        ])->where('doctor_profile_id', $profile->id)->findOrFail($id);

        return response()->json(['data' => $appointment]);
    }

    public function doctorCalendar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'clinic_id' => ['sometimes', 'integer', 'exists:clinics,id'],
        ]);
        $profile = $request->user()->doctorProfile;
        abort_unless($profile, 404, 'Lukman profili tapylmady.');

        $appointments = Appointment::with(['patient:id,name,phone,avatar_path', 'clinic:id,name,timezone', 'payment:id,appointment_id,status,amount,currency'])
            ->where('doctor_profile_id', $profile->id)
            ->whereBetween('appointment_date', [$data['from'], $data['to']])
            ->when(isset($data['clinic_id']), fn ($q) => $q->where('clinic_id', $data['clinic_id']))
            ->orderBy('appointment_date')->orderBy('start_time')->get();

        return response()->json(['data' => $appointments]);
    }

    public function doctorReviews(Request $request): JsonResponse
    {
        $profile = $request->user()->doctorProfile;
        abort_unless($profile, 404, 'Lukman profili tapylmady.');

        $reviews = Review::with(['patient:id,name,avatar_path', 'appointment:id,booking_code,appointment_date'])
            ->where('doctor_profile_id', $profile->id)
            ->latest()
            ->paginate(20);

        return response()->json($reviews);
    }

    public function doctorAppointments(Request $request): JsonResponse
    {
        $profile=$request->user()->doctorProfile;
        abort_unless($profile,404,'Lukman profili tapylmady.');
        return response()->json(Appointment::with(['patient','clinic','payment','review'])->where('doctor_profile_id',$profile->id)
            ->when($request->filled('status'),fn($q)=>$q->where('status',$request->string('status')))
            ->when($request->filled('date'),fn($q)=>$q->whereDate('appointment_date',$request->date))
            ->orderBy('appointment_date')->orderBy('start_time')->paginate(20));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $v=$request->validate(['status'=>'required|in:checked_in,completed,no_show']);
        $profile=$request->user()->doctorProfile;
        abort_unless($profile,404,'Lukman profili tapylmady.');
        $a=DB::transaction(function() use($profile,$id,$v) {
            $a=Appointment::where('id',$id)->where('doctor_profile_id',$profile->id)->lockForUpdate()->firstOrFail();
            $allowed=['confirmed'=>['checked_in','no_show'],'checked_in'=>['completed']];
            if(!in_array($v['status'],$allowed[$a->status]??[],true)) abort(422,'Bu status üýtgemesine rugsat berilmeýär.');
            $updates=['status'=>$v['status']];
            if($v['status']==='completed') $updates['completed_at']=now();
            $a->update($updates);
            $this->notify($a->patient_id,'Kabul ýazgysy täzelendi','Kabul ýazgyňyzyň ýagdaýy: '.$v['status'],'appointment_'.$v['status'],['appointment_id'=>$a->id]);
            return $a;
        });
        return response()->json(['message'=>'Kabul ýazgysynyň ýagdaýy täzelendi.','data'=>$a]);
    }

    public function storeForPatient(Request $request): JsonResponse
    {
        $v=$request->validate(['patient_id'=>'required|exists:users,id','doctor_profile_id'=>'required|exists:doctor_profiles,id','clinic_id'=>'required|exists:clinics,id','appointment_date'=>'required|date_format:Y-m-d','start_time'=>'required|date_format:H:i','patient_phone'=>'required|string|max:30','reason'=>'nullable|string|max:1000']);
        $clinicAllowed=$request->user()->belongsToClinic((int)$v['clinic_id']);
        abort_unless($clinicAllowed,403,'Bu klinika üçin rugsadyňyz ýok.');
        $patient=\App\Models\User::where('id',$v['patient_id'])->where('role','patient')->firstOrFail();
        $a=DB::transaction(function() use($v,$request,$patient) {
            $doctorProfile=DoctorProfile::whereKey($v['doctor_profile_id'])->where('is_verified',true)
                ->whereHas('user',fn($q)=>$q->where('is_active',true))->lockForUpdate()->firstOrFail();
            abort_unless(\App\Models\Clinic::whereKey($v['clinic_id'])->where('is_active',true)->exists(),422,'Saýlanan klinika işjeň däl.');
            $schedule=DoctorSchedule::where('doctor_profile_id',$v['doctor_profile_id'])->where('clinic_id',$v['clinic_id'])->where('day_of_week',Carbon::parse($v['appointment_date'], \App\Models\Clinic::whereKey($v['clinic_id'])->value('timezone') ?: config('app.timezone'))->dayOfWeekIso)->where('is_active',true)->lockForUpdate()->first();
            if(!$schedule) abort(422,'Lukman bu gün kabul etmeýär.');
            $start=Carbon::createFromFormat('H:i',$v['start_time']); $end=$start->copy()->addMinutes($schedule->slot_duration_minutes);
            $this->availability->assertAvailable($v['doctor_profile_id'],$v['clinic_id'],$v['appointment_date'],$start->format('H:i:s'),$end->format('H:i:s'));
            $a=Appointment::create(['booking_code'=>'ST-'.date('Y').'-'.strtoupper(\Illuminate\Support\Str::random(8)),'patient_id'=>$patient->id,'doctor_profile_id'=>$v['doctor_profile_id'],'clinic_id'=>$v['clinic_id'],'created_by_user_id'=>$request->user()->id,'appointment_date'=>$v['appointment_date'],'start_time'=>$start->format('H:i:s'),'end_time'=>$end->format('H:i:s'),'duration_minutes'=>$schedule->slot_duration_minutes,'status'=>'pending','patient_phone'=>$v['patient_phone'],'reason'=>$v['reason']??null,'notes'=>null]);
            $this->notify($patient->id,'Kabul ýazgysy döredildi','Registratura tarapyndan kabul ýazgysy döredildi.','appointment_created',['appointment_id'=>$a->id]);
            $doctor=DoctorProfile::findOrFail($v['doctor_profile_id']);
            Payment::create(['appointment_id'=>$a->id,'amount'=>$doctor->consultation_fee,'currency'=>$doctor->currency?:'TMT','status'=>'pending','payment_method'=>'cash']);
            return $a;
        },3);
        return response()->json(['message'=>'Kabul ýazgysy döredildi.','data'=>$a],201);
    }

    public function doctorDashboard(Request $request): JsonResponse
    {
        $profile=$request->user()->doctorProfile;
        abort_unless($profile,404,'Lukman profili tapylmady.');
        $base=Appointment::where('doctor_profile_id',$profile->id);
        return response()->json([
            'today'=>(clone $base)->whereDate('appointment_date',today())->count(),
            'pending'=>(clone $base)->where('status','pending')->count(),
            'confirmed'=>(clone $base)->where('status','confirmed')->count(),
            'completed'=>(clone $base)->where('status','completed')->count(),
        ]);
    }
}
