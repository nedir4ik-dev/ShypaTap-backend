<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Appointment,Clinic,DoctorProfile,Payment,User};
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'clinic_id' => ['sometimes','integer','exists:clinics,id'],
            'doctor_profile_id' => ['sometimes','integer','exists:doctor_profiles,id'],
            'month' => ['sometimes','date_format:Y-m'],
            'from' => ['sometimes','date_format:Y-m-d'], 'to' => ['sometimes','date_format:Y-m-d','after_or_equal:from'],
            'payment_status' => ['sometimes','in:pending,paid,refunded,cancelled'],
            'status' => ['sometimes','in:pending,confirmed,checked_in,cancelled,rejected,completed,no_show'],
        ]);
        $user=$request->user();
        $clinicIds = in_array(strtolower((string)$user->role), ['superadmin','super_admin'], true) ? Clinic::query()->pluck('id') : $user->staffClinics()->pluck('clinics.id');
        if(isset($filters['clinic_id'])) abort_unless($clinicIds->contains((int)$filters['clinic_id']),403,'Bu klinika üçin rugsadyňyz ýok.');
        if(isset($filters['clinic_id'])) $clinicIds=collect([(int)$filters['clinic_id']]);
        $appointments = Appointment::query()->whereIn('clinic_id',$clinicIds)
            ->when(isset($filters['doctor_profile_id']),fn($q)=>$q->where('doctor_profile_id',$filters['doctor_profile_id']))
            ->when(isset($filters['month']),fn($q)=>$q->whereRaw("to_char(appointment_date, 'YYYY-MM') = ?",[$filters['month']]))
            ->when(isset($filters['from']),fn($q)=>$q->whereDate('appointment_date','>=',$filters['from']))
            ->when(isset($filters['to']),fn($q)=>$q->whereDate('appointment_date','<=',$filters['to']))
            ->when(isset($filters['status']),fn($q)=>$q->where('status',$filters['status']));
        $paymentQuery=Payment::query()->whereHas('appointment',function($q)use($filters,$clinicIds){
            $q->whereIn('clinic_id',$clinicIds)->when(isset($filters['doctor_profile_id']),fn($x)=>$x->where('doctor_profile_id',$filters['doctor_profile_id']))
                ->when(isset($filters['month']),fn($x)=>$x->whereRaw("to_char(appointment_date, 'YYYY-MM') = ?",[$filters['month']]))
                ->when(isset($filters['from']),fn($x)=>$x->whereDate('appointment_date','>=',$filters['from']))
                ->when(isset($filters['to']),fn($x)=>$x->whereDate('appointment_date','<=',$filters['to']));
        })->when(isset($filters['payment_status']),fn($q)=>$q->where('status',$filters['payment_status']));
        $paid=(clone $paymentQuery)->where('status','paid');
        $doctorQuery=DoctorProfile::query()->whereHas('clinics',fn($q)=>$q->whereIn('clinics.id',$clinicIds));
        $patientQuery=User::query()->where('role','patient')->whereHas('patientAppointments',fn($q)=>$q->whereIn('clinic_id',$clinicIds));
        return response()->json([
            'total_users'=>User::where(function($q)use($clinicIds){$q->whereHas('staffClinics',fn($x)=>$x->whereIn('clinics.id',$clinicIds))->orWhereHas('doctorProfile.clinics',fn($x)=>$x->whereIn('clinics.id',$clinicIds))->orWhereHas('patientAppointments',fn($x)=>$x->whereIn('clinic_id',$clinicIds));})->count(),
            'total_patients'=>(clone $patientQuery)->count(), 'total_doctors'=>(clone $doctorQuery)->count(),
            'total_receptionists'=>User::where('role','receptionist')->whereHas('staffClinics',fn($q)=>$q->whereIn('clinics.id',$clinicIds))->count(),
            'total_clinics'=>Clinic::whereIn('id',$clinicIds)->count(), 'total_appointments'=>(clone $appointments)->count(),
            'pending_appointments'=>(clone $appointments)->where('status','pending')->count(),
            'confirmed_appointments'=>(clone $appointments)->where('status','confirmed')->count(),
            'checked_in_appointments'=>(clone $appointments)->where('status','checked_in')->count(),
            'cancelled_appointments'=>(clone $appointments)->where('status','cancelled')->count(),
            'completed_appointments'=>(clone $appointments)->where('status','completed')->count(),
            'no_show_appointments'=>(clone $appointments)->where('status','no_show')->count(),
            'pending_payments'=>(clone $paymentQuery)->where('status','pending')->count(),
            'refunded_payments'=>(clone $paymentQuery)->where('status','refunded')->count(),
            'paid_total'=>(clone $paid)->sum('amount'),'paid_count'=>(clone $paid)->count(),
            'revenue_by_month'=>(clone $paid)->whereNotNull('paid_at')->selectRaw("to_char(paid_at, 'YYYY-MM') as month, SUM(amount) as total")->groupBy('month')->orderBy('month')->get(),
            'recent_appointments'=>(clone $appointments)->with(['patient:id,name','doctor.user:id,name','clinic:id,name'])->latest()->take(8)->get(),
            'filters'=>$filters,
        ]);
    }
}
