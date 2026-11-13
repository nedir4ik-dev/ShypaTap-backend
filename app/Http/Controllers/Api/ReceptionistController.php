<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
class ReceptionistController extends Controller {
 private function clinicIds(Request $r) { return $r->user()->staffClinics()->pluck('clinics.id'); }
 public function dashboard(Request $r) {
  $ids=$this->clinicIds($r); $q=Appointment::whereIn('clinic_id',$ids);
  return response()->json(['appointments'=>$q->count(),'pending'=>(clone $q)->where('status','pending')->count(),'confirmed'=>(clone $q)->where('status','confirmed')->count(),'today'=>(clone $q)->whereDate('appointment_date',today())->count()]);
 }
 public function appointments(Request $r) {
  $ids=$this->clinicIds($r);
  abort_if($ids->isEmpty(),403,'Siziň hasabyňyza klinika birikdirilmedik.');
  return response()->json(Appointment::with(['patient','doctor.user','clinic'])->whereIn('clinic_id',$ids)
   ->when($r->filled('status'),fn($q)=>$q->where('status',$r->status))
   ->when($r->filled('date'),fn($q)=>$q->whereDate('appointment_date',$r->date))
   ->orderBy('appointment_date')->orderBy('start_time')->paginate(20));
 }
 public function patients(Request $r) {
  $ids=$this->clinicIds($r);
  return response()->json(User::where('role','patient')->whereHas('patientAppointments',fn($q)=>$q->whereIn('clinic_id',$ids))->select('id','name','phone','email')->paginate(20));
 }
 public function doctors(Request $r) {
  $ids=$this->clinicIds($r);
  return response()->json(\App\Models\DoctorProfile::with(['user','specialization','clinics'])->whereHas('clinics',fn($q)=>$q->whereIn('clinics.id',$ids))->paginate(20));
 }
}
