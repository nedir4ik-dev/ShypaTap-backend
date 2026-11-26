<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
class ReceptionistController extends Controller {
 private function clinicIds(Request $r) {
  $ids=$r->user()->staffClinics()->pluck('clinics.id');
  if($r->filled('clinic_id')) { $id=(int)$r->query('clinic_id'); abort_unless($ids->contains($id),403,'Bu klinika siziň hasabyňyza birikdirilmedik.'); return collect([$id]); }
  return $ids;
 }
 public function dashboard(Request $r) {
  $ids=$this->clinicIds($r); $q=Appointment::whereIn('clinic_id',$ids);
  return response()->json(['appointments'=>$q->count(),'pending'=>(clone $q)->where('status','pending')->count(),'confirmed'=>(clone $q)->where('status','confirmed')->count(),'today'=>(clone $q)->whereDate('appointment_date',today())->count(),'clinics'=>$r->user()->staffClinics()->select('clinics.id','clinics.name')->get()]);
 }
 public function appointments(Request $r) {
  $ids=$this->clinicIds($r); abort_if($ids->isEmpty(),403,'Siziň hasabyňyza klinika birikdirilmedik.');
  return response()->json(Appointment::select(['id','booking_code','patient_id','doctor_profile_id','clinic_id','appointment_date','start_time','end_time','duration_minutes','status','patient_phone','created_at'])->with(['patient:id,name,phone,date_of_birth','doctor.user:id,name','clinic:id,name'])
   ->whereIn('clinic_id',$ids)->when($r->filled('status'),fn($q)=>$q->where('status',$r->string('status')))
   ->when($r->filled('date'),fn($q)=>$q->whereDate('appointment_date',$r->date))
   ->orderBy('appointment_date')->orderBy('start_time')->paginate(20));
 }
 public function patients(Request $r) {
  $ids=$this->clinicIds($r);
  return response()->json(User::where('role','patient')->whereHas('patientAppointments',fn($q)=>$q->whereIn('clinic_id',$ids))
   ->select('id','name','phone','date_of_birth')->with(['patientAppointments'=>fn($q)=>$q->whereIn('clinic_id',$ids)->select('id','patient_id','clinic_id','booking_code','appointment_date','start_time','status')->latest('appointment_date')->limit(10)])
   ->paginate(20));
 }
 public function doctors(Request $r) {
  $ids=$this->clinicIds($r);
  return response()->json(\App\Models\DoctorProfile::with(['user:id,name,avatar_path','specialization:id,name','clinics:id,name'])->whereHas('clinics',fn($q)=>$q->whereIn('clinics.id',$ids))->paginate(20));
 }
}
