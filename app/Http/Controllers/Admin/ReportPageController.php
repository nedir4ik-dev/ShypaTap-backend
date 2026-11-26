<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Appointment, Clinic, Payment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportPageController extends Controller
{
    private function clinicIds(Request $request)
    {
        return in_array(strtolower((string)$request->user()->role), ['superadmin', 'super_admin'], true) ? Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id');
    }
    public function index(Request $request)
    {
        $ids = $this->clinicIds($request);
        $filters = $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from', 'clinic_id' => 'nullable|integer|exists:clinics,id']);
        if (isset($filters['clinic_id'])) {
            abort_unless($ids->contains((int)$filters['clinic_id']), 403);
            $ids = collect([(int)$filters['clinic_id']]);
        }
        $appointments = Appointment::whereIn('clinic_id', $ids)->when(isset($filters['from']), fn($q) => $q->whereDate('appointment_date', '>=', $filters['from']))->when(isset($filters['to']), fn($q) => $q->whereDate('appointment_date', '<=', $filters['to']));
        $payments = Payment::whereHas('appointment', fn($q) => $q->whereIn('clinic_id', $ids)->when(isset($filters['from']), fn($x) => $x->whereDate('appointment_date', '>=', $filters['from']))->when(isset($filters['to']), fn($x) => $x->whereDate('appointment_date', '<=', $filters['to'])));
        $stats = ['total' => (clone $appointments)->count(), 'completed' => (clone $appointments)->where('status', 'completed')->count(), 'cancelled' => (clone $appointments)->where('status', 'cancelled')->count(), 'no_show' => (clone $appointments)->where('status', 'no_show')->count(), 'revenue' => (clone $payments)->where('status', 'paid')->sum('amount'), 'pending' => (clone $payments)->where('status', 'pending')->sum('amount'), 'refunded' => (clone $payments)->where('status', 'refunded')->sum('amount')];
        $clinics = Clinic::whereIn('id', $this->clinicIds($request))->orderBy('name')->get(['id', 'name']);
        $doctorPerformance = DB::table('appointments')->join('doctor_profiles', 'doctor_profiles.id', '=', 'appointments.doctor_profile_id')->join('users', 'users.id', '=', 'doctor_profiles.user_id')->whereIn('appointments.clinic_id', $ids)->when(isset($filters['from']), fn($q) => $q->whereDate('appointments.appointment_date', '>=', $filters['from']))->when(isset($filters['to']), fn($q) => $q->whereDate('appointments.appointment_date', '<=', $filters['to']))->select('doctor_profiles.id', 'users.name')->selectRaw("COUNT(appointments.id) as total, SUM(CASE WHEN appointments.status = 'completed' THEN 1 ELSE 0 END) as completed")->groupBy('doctor_profiles.id', 'users.name')->orderByDesc('total')->get();
        $clinicPerformance = DB::table('appointments')->join('clinics', 'clinics.id', '=', 'appointments.clinic_id')->whereIn('appointments.clinic_id', $ids)->when(isset($filters['from']), fn($q) => $q->whereDate('appointments.appointment_date', '>=', $filters['from']))->when(isset($filters['to']), fn($q) => $q->whereDate('appointments.appointment_date', '<=', $filters['to']))->select('clinics.id', 'clinics.name')->selectRaw("COUNT(appointments.id) as total, SUM(CASE WHEN appointments.status = 'completed' THEN 1 ELSE 0 END) as completed")->groupBy('clinics.id', 'clinics.name')->orderByDesc('total')->get();
        return view('admin.reports.index', compact('stats', 'filters', 'clinics', 'doctorPerformance', 'clinicPerformance'));
    }
    public function export(Request $request): StreamedResponse
    {
        $ids = $this->clinicIds($request);
        $filters = $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from', 'clinic_id' => 'nullable|integer|exists:clinics,id']);
        if (isset($filters['clinic_id'])) {
            abort_unless($ids->contains((int)$filters['clinic_id']), 403);
            $ids = collect([(int)$filters['clinic_id']]);
        }
        $query = Appointment::with(['patient:id,name,phone', 'doctor.user:id,name', 'clinic:id,name', 'payment'])->whereIn('clinic_id', $ids)->when(isset($filters['from']), fn($q) => $q->whereDate('appointment_date', '>=', $filters['from']))->when(isset($filters['to']), fn($q) => $q->whereDate('appointment_date', '<=', $filters['to']))->orderBy('appointment_date')->orderBy('start_time');
        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Booking', 'Date', 'Start', 'End', 'Clinic', 'Doctor', 'Patient', 'Status', 'Payment status', 'Amount', 'Currency']);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $a) fputcsv($out, [$a->booking_code, $a->appointment_date?->toDateString(), $a->start_time, $a->end_time, $a->clinic?->name, $a->doctor?->user?->name, $a->patient?->name, $a->status, $a->payment?->status, $a->payment?->amount, $a->payment?->currency]);
            });
            fclose($out);
        }, 'shypatap-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
