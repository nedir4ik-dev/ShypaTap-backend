<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Appointment,Clinic,DoctorProfile,Payment,User};
use Illuminate\Http\Request;
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $clinicIds=in_array(strtolower((string)$request->user()->role),['superadmin','super_admin'],true)?Clinic::pluck('id'):$request->user()->staffClinics()->pluck('clinics.id');
        $filters=$request->validate(['clinic_id'=>'nullable|integer|exists:clinics,id','doctor_profile_id'=>'nullable|integer|exists:doctor_profiles,id','month'=>'nullable|date_format:Y-m','status'=>'nullable|in:pending,confirmed,checked_in,cancelled,rejected,completed,no_show','payment_status'=>'nullable|in:pending,paid,refunded,cancelled']);
        if(isset($filters['clinic_id'])){abort_unless($clinicIds->contains((int)$filters['clinic_id']),403);$clinicIds=collect([(int)$filters['clinic_id']]);}
        $appointments=Appointment::whereIn('clinic_id',$clinicIds)->when(isset($filters['doctor_profile_id']),fn($q)=>$q->where('doctor_profile_id',$filters['doctor_profile_id']))->when(isset($filters['month']),fn($q)=>$q->whereRaw("to_char(appointment_date,'YYYY-MM') = ?",[$filters['month']]))->when(isset($filters['status']),fn($q)=>$q->where('status',$filters['status']));
        $payments=Payment::whereHas('appointment',fn($q)=>$q->whereIn('clinic_id',$clinicIds)->when(isset($filters['doctor_profile_id']),fn($x)=>$x->where('doctor_profile_id',$filters['doctor_profile_id']))->when(isset($filters['month']),fn($x)=>$x->whereRaw("to_char(appointment_date,'YYYY-MM') = ?",[$filters['month']])))->when(isset($filters['payment_status']),fn($q)=>$q->where('status',$filters['payment_status']));
        $paid=(clone $payments)->where('status','paid');
        $totalUsers=User::where(function($q)use($clinicIds){$q->whereHas('staffClinics',fn($x)=>$x->whereIn('clinics.id',$clinicIds))->orWhereHas('doctorProfile.clinics',fn($x)=>$x->whereIn('clinics.id',$clinicIds))->orWhereHas('patientAppointments',fn($x)=>$x->whereIn('clinic_id',$clinicIds));})->count();
        $totalPatients=User::where('role','patient')->whereHas('patientAppointments',fn($q)=>$q->whereIn('clinic_id',$clinicIds))->count();
        $totalDoctors=DoctorProfile::whereHas('clinics',fn($q)=>$q->whereIn('clinics.id',$clinicIds))->count();
        $totalClinics=Clinic::whereIn('id',$clinicIds)->count();
        $recentAppointments=(clone $appointments)->with(['patient:id,name','doctor.user:id,name','doctor.specialization','clinic:id,name'])->latest('appointment_date')->take(8)->get();
        $totalAppointments=(clone $appointments)->count();
        $todayAppointments=(clone $appointments)->whereDate('appointment_date',today())->count();
        $completedAppointments=(clone $appointments)->where('status','completed')->count();
        $cancelledAppointments=(clone $appointments)->where('status','cancelled')->count();
        $pendingPayments=(clone $payments)->where('status','pending')->count();
        $paidPayments=(clone $paid)->count();
        $revenue=(clone $paid)->sum('amount');
        $revenueByMonth=(clone $paid)->whereNotNull('paid_at')->selectRaw("to_char(paid_at,'YYYY-MM') as month, SUM(amount) as total")->groupBy('month')->orderBy('month')->get();
        $clinics=Clinic::whereIn('id',in_array(strtolower((string)$request->user()->role),['superadmin','super_admin'],true)?Clinic::pluck('id'):$request->user()->staffClinics()->pluck('clinics.id'))->orderBy('name')->get(['id','name']);
        $doctors=DoctorProfile::with('user:id,name')->whereHas('clinics',fn($q)=>$q->whereIn('clinics.id',$clinicIds))->get(['id','user_id']);
        return view('admin.dashboard',compact('totalUsers','totalPatients','totalDoctors','totalClinics','recentAppointments','totalAppointments','todayAppointments','completedAppointments','cancelledAppointments','pendingPayments','paidPayments','revenue','revenueByMonth','clinics','doctors','filters'));
    }
}
