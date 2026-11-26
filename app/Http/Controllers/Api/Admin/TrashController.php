<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Appointment,Clinic,DoctorProfile,Review,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TrashController extends Controller
{
    private const TYPES = [
        'users' => User::class, 'doctors' => DoctorProfile::class, 'clinics' => Clinic::class,
        'appointments' => Appointment::class, 'reviews' => Review::class,
    ];

    public function index(Request $request)
    {
        $type = $request->query('type');
        $items = [];
        foreach (self::TYPES as $key => $model) {
            if ($type && $type !== $key) continue;
            $query = $model::onlyTrashed();
            if (!$this->isSuperAdmin($request)) $query = $this->scopeTrashQuery($request, $key, $query);
            $items[$key] = $query->latest('deleted_at')->paginate(20, ['*'], $key.'_page');
        }
        if ($request->expectsJson() || $request->is('api/*')) return response()->json(['data'=>$items]);
        return view('admin.trash.index', compact('items'));
    }

    public function restore(Request $request, string $type, int $id)
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $model = self::TYPES[$type];
        $record = $model::onlyTrashed()->findOrFail($id);
        $this->assertTrashScope($request, $type, $record);
        DB::transaction(function () use ($type, $record) {
            $batch = $record->trash_batch_id;
            $record->restore();
            if ($batch && $type === 'appointments') {
                Review::onlyTrashed()->where('trash_batch_id',$batch)->get()->each->restore();
            }
            if ($batch && $type === 'doctors') {
                User::onlyTrashed()->where('trash_batch_id',$batch)->get()->each->restore();
                Appointment::onlyTrashed()->where('trash_batch_id',$batch)->get()->each->restore();
                Review::onlyTrashed()->where('trash_batch_id',$batch)->get()->each->restore();
            }
            if ($batch && $type === 'users') {
                DoctorProfile::onlyTrashed()->where('trash_batch_id',$batch)->get()->each->restore();
                Appointment::onlyTrashed()->where('trash_batch_id',$batch)->get()->each->restore();
                Review::onlyTrashed()->where('trash_batch_id',$batch)->get()->each->restore();
            }
            if ($type === 'clinics' && $batch) {
                foreach ([Appointment::class, Review::class, DoctorProfile::class] as $related) {
                    $related::onlyTrashed()->where('trash_batch_id',$batch)->get()->each->restore();
                }
            }
        });
        return ($request->expectsJson() || $request->is('api/*')) ? response()->json(['message'=>'Ýazgy dikeldildi.']) : back()->with('success','Ýazgy dikeldildi.');
    }

    public function forceDelete(Request $request, string $type, int $id)
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $record = self::TYPES[$type]::onlyTrashed()->findOrFail($id);
        $this->assertTrashScope($request, $type, $record);
        if ($record instanceof Appointment && $record->payment()->exists()) {
            return response()->json(['message'=>'Bu kabul ýazgysynda töleg taryhy bar. Maliýe maglumatlaryny goramak üçin hemişelik pozup bolmaýar.'], 422);
        }
        if ($record instanceof Clinic && $record->appointments()->withTrashed()->whereHas('payment')->exists()) {
            return response()->json(['message'=>'Klinikada töleg taryhy bar. Maliýe maglumatlaryny goramak üçin hemişelik pozup bolmaýar.'], 422);
        }
        try { $record->forceDelete(); } catch (\Throwable $e) {
            report($e);
            return response()->json(['message'=>'Baglanyşykly ýazgylar bar. Maglumat bitewüligini goramak üçin hemişelik pozmak gadagan edildi.'], 422);
        }
        return ($request->expectsJson() || $request->is('api/*')) ? response()->json(['message'=>'Ýazgy hemişelik pozuldy.']) : back()->with('success','Ýazgy hemişelik pozuldy.');
    }

    private function isSuperAdmin(Request $request): bool
    {
        return in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true);
    }

    private function assignedClinicIds(Request $request): array
    {
        return DB::table('clinic_staff')->where('user_id',$request->user()->id)->pluck('clinic_id')->map(fn($id)=>(int)$id)->all();
    }

    private function scopedUserIds(array $clinicIds): array
    {
        if (!$clinicIds) return [];
        $staff=DB::table('clinic_staff')->whereIn('clinic_id',$clinicIds)->pluck('user_id');
        $doctors=DB::table('doctor_profiles')->join('clinic_doctors','clinic_doctors.doctor_profile_id','=','doctor_profiles.id')->whereIn('clinic_doctors.clinic_id',$clinicIds)->pluck('doctor_profiles.user_id');
        $patients=DB::table('appointments')->whereIn('clinic_id',$clinicIds)->pluck('patient_id');
        $creators=DB::table('appointments')->whereIn('clinic_id',$clinicIds)->whereNotNull('created_by_user_id')->pluck('created_by_user_id');
        return $staff->concat($doctors)->concat($patients)->concat($creators)->unique()->map(fn($id)=>(int)$id)->all();
    }

    private function scopeTrashQuery(Request $request, string $type, $query)
    {
        $clinicIds=$this->assignedClinicIds($request);
        return match($type) {
            'clinics' => $query->whereIn('id',$clinicIds),
            'appointments' => $query->whereIn('clinic_id',$clinicIds),
            'reviews' => $query->whereHas('appointment',fn($q)=>$q->whereIn('clinic_id',$clinicIds)),
            'doctors' => $query->whereIn('id',DB::table('clinic_doctors')->whereIn('clinic_id',$clinicIds)->pluck('doctor_profile_id')),
            'users' => $query->whereIn('id',$this->scopedUserIds($clinicIds)),
            default => $query->whereRaw('1 = 0'),
        };
    }

    private function assertTrashScope(Request $request, string $type, $record): void
    {
        if ($this->isSuperAdmin($request)) return;
        $clinicIds=$this->assignedClinicIds($request);
        $allowed=match($type) {
            'clinics' => in_array((int)$record->id,$clinicIds,true),
            'appointments' => in_array((int)$record->clinic_id,$clinicIds,true),
            'reviews' => $record->appointment && in_array((int)$record->appointment->clinic_id,$clinicIds,true),
            'doctors' => DB::table('clinic_doctors')->where('doctor_profile_id',$record->id)->whereIn('clinic_id',$clinicIds)->exists(),
            'users' => in_array((int)$record->id,$this->scopedUserIds($clinicIds),true),
            default => false,
        };
        abort_unless($allowed,403,'Bu ýazgy siziň klinikalaryňyza degişli däl.');
    }

}
