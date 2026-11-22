<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Payment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function filters(Request $request): array
    {
        return $request->validate([
            'clinic_id' => ['nullable', 'integer', 'exists:clinics,id'],
            'doctor_profile_id' => ['nullable', 'integer', 'exists:doctor_profiles,id'],
            'month' => ['nullable', 'date_format:Y-m'],
            'payment_status' => ['nullable', 'in:pending,paid,refunded,cancelled'],
        ]);
    }

    private function query(array $filters)
    {
        return Appointment::query()
            ->with(['patient:id,name,phone', 'doctor.user:id,name', 'clinic:id,name', 'payment'])
            ->when(!empty($filters['clinic_id']), fn ($q) => $q->where('clinic_id', $filters['clinic_id']))
            ->when(!empty($filters['doctor_profile_id']), fn ($q) => $q->where('doctor_profile_id', $filters['doctor_profile_id']))
            ->when(!empty($filters['month']), fn ($q) => $q->whereRaw("to_char(appointment_date, 'YYYY-MM') = ?", [$filters['month']]))
            ->when(!empty($filters['payment_status']), fn ($q) => $q->whereHas('payment', fn ($p) => $p->where('status', $filters['payment_status'])));
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $appointments = $this->query($filters)->orderByDesc('appointment_date')->orderByDesc('start_time')->paginate(25)->withQueryString();
        $payments = Payment::query()->whereHas('appointment', function ($q) use ($filters) {
            $q->when(!empty($filters['clinic_id']), fn ($x) => $x->where('clinic_id', $filters['clinic_id']))
                ->when(!empty($filters['doctor_profile_id']), fn ($x) => $x->where('doctor_profile_id', $filters['doctor_profile_id']))
                ->when(!empty($filters['month']), fn ($x) => $x->whereRaw("to_char(appointment_date, 'YYYY-MM') = ?", [$filters['month']]));
        })->when(!empty($filters['payment_status']), fn ($q) => $q->where('status', $filters['payment_status']));

        return view('admin.reports.index', [
            'filters' => $filters,
            'appointments' => $appointments,
            'clinics' => Clinic::orderBy('name')->get(['id', 'name']),
            'doctors' => DoctorProfile::with('user:id,name')->get(),
            'total' => (clone $payments)->count(),
            'pending' => (clone $payments)->where('status', 'pending')->count(),
            'paid' => (clone $payments)->where('status', 'paid')->count(),
            'refunded' => (clone $payments)->where('status', 'refunded')->count(),
            'revenueByCurrency' => (clone $payments)->where('status', 'paid')->selectRaw('currency, SUM(amount) as total')->groupBy('currency')->get(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $rows = $this->query($filters)->orderBy('appointment_date')->orderBy('start_time')->get();
        $filename = 'shypatap-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Bron kody', 'Sene', 'Wagt', 'Näsag', 'Lukman', 'Klinika', 'Bron ýagdaýy', 'Töleg ýagdaýy', 'Mukdar', 'Walýuta']);
            foreach ($rows as $a) {
                fputcsv($out, [
                    $a->booking_code, $a->appointment_date?->format('Y-m-d'), $a->start_time,
                    $a->patient?->name, $a->doctor?->user?->name, $a->clinic?->name,
                    $a->status, $a->payment?->status, $a->payment?->amount, $a->payment?->currency,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
