<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $clinicIds = $this->availableClinicIds($request);
        $filters = $request->validate([
            'clinic_id' => ['nullable', 'integer', 'exists:clinics,id'],
            'doctor_profile_id' => ['nullable', 'integer', 'exists:doctor_profiles,id'],
            'month' => ['nullable', 'date_format:Y-m'],
            'status' => ['nullable', 'in:pending,confirmed,checked_in,cancelled,rejected,completed,no_show'],
            'payment_status' => ['nullable', 'in:pending,paid,refunded,cancelled'],
        ]);

        if (isset($filters['clinic_id'])) {
            abort_unless($clinicIds->contains((int) $filters['clinic_id']), 403);
            $clinicIds = collect([(int) $filters['clinic_id']]);
        }

        $doctorId = $filters['doctor_profile_id'] ?? null;

        if ($doctorId && ! DoctorProfile::whereKey($doctorId)
            ->whereHas('clinics', fn (Builder $query) => $query->whereIn('clinics.id', $clinicIds))
            ->exists()) {
            abort(403);
        }

        $appointments = Appointment::query()
            ->whereIn('clinic_id', $clinicIds)
            ->when($doctorId, fn (Builder $query) => $query->where('doctor_profile_id', $doctorId))
            ->when($filters['month'] ?? null, fn (Builder $query, string $month) => $this->forMonth($query, 'appointment_date', $month))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));

        $payments = Payment::query()
            ->whereHas('appointment', function (Builder $query) use ($clinicIds, $doctorId, $filters): void {
                $query->whereIn('clinic_id', $clinicIds)
                    ->when($doctorId, fn (Builder $appointmentQuery) => $appointmentQuery->where('doctor_profile_id', $doctorId))
                    ->when($filters['month'] ?? null, fn (Builder $appointmentQuery, string $month) => $this->forMonth($appointmentQuery, 'appointment_date', $month));
            })
            ->when($filters['payment_status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));

        $paidPayments = (clone $payments)->where('status', 'paid');

        return view('admin.dashboard', [
            'totalUsers' => User::query()->where(function (Builder $query) use ($clinicIds): void {
                $query->whereHas('staffClinics', fn (Builder $clinicQuery) => $clinicQuery->whereIn('clinics.id', $clinicIds))
                    ->orWhereHas('doctorProfile.clinics', fn (Builder $clinicQuery) => $clinicQuery->whereIn('clinics.id', $clinicIds))
                    ->orWhereHas('patientAppointments', fn (Builder $appointmentQuery) => $appointmentQuery->whereIn('clinic_id', $clinicIds));
            })->count(),
            'totalPatients' => User::query()->where('role', 'patient')->whereHas('patientAppointments', fn (Builder $query) => $query->whereIn('clinic_id', $clinicIds))->count(),
            'totalDoctors' => DoctorProfile::query()->whereHas('clinics', fn (Builder $query) => $query->whereIn('clinics.id', $clinicIds))->count(),
            'totalClinics' => Clinic::query()->whereIn('id', $clinicIds)->count(),
            'totalAppointments' => (clone $appointments)->count(),
            'todayAppointments' => (clone $appointments)->whereDate('appointment_date', today())->count(),
            'completedAppointments' => (clone $appointments)->where('status', 'completed')->count(),
            'cancelledAppointments' => (clone $appointments)->where('status', 'cancelled')->count(),
            'pendingPayments' => (clone $payments)->where('status', 'pending')->count(),
            'paidPayments' => (clone $paidPayments)->count(),
            'revenue' => (clone $paidPayments)->sum('amount'),
            'revenueByMonth' => $this->revenueByMonth((clone $paidPayments)->whereNotNull('paid_at')),
            'clinics' => Clinic::query()->whereIn('id', $this->availableClinicIds($request))->orderBy('name')->get(['id', 'name']),
            'doctors' => DoctorProfile::query()->with('user:id,name')->whereHas('clinics', fn (Builder $query) => $query->whereIn('clinics.id', $clinicIds))->orderBy('id')->get(['id', 'user_id']),
            'filters' => $filters,
        ]);
    }

    /** @return Collection<int, int> */
    private function availableClinicIds(Request $request): Collection
    {
        if (in_array(strtolower((string) $request->user()->role), ['superadmin', 'super_admin'], true)) {
            return Clinic::query()->pluck('id');
        }

        return $request->user()->staffClinics()->pluck('clinics.id');
    }

    private function forMonth(Builder $query, string $column, string $month): Builder
    {
        $start = CarbonImmutable::createFromFormat('Y-m', $month)->startOfMonth();

        return $query->where($column, '>=', $start->toDateString())
            ->where($column, '<', $start->addMonth()->toDateString());
    }

    private function revenueByMonth(Builder $payments): Collection
    {
        $monthExpression = match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', paid_at)",
            'mysql', 'mariadb' => "date_format(paid_at, '%Y-%m')",
            default => "to_char(paid_at, 'YYYY-MM')",
        };

        return $payments->selectRaw("{$monthExpression} as month, sum(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
