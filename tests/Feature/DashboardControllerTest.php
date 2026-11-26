<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_filtered_metrics_and_responsive_dashboard_layout(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $clinic = Clinic::factory()->create();
        $doctor = DoctorProfile::factory()->create();
        $doctor->clinics()->attach($clinic);
        $patient = User::factory()->patient()->create();
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic,
            'doctor_profile_id' => $doctor,
            'patient_id' => $patient,
            'appointment_date' => '2026-11-10',
            'status' => 'completed',
        ]);
        Payment::query()->create([
            'appointment_id' => $appointment->id,
            'amount' => 125.50,
            'status' => 'paid',
            'paid_at' => '2026-11-10 09:30:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'clinic_id' => $clinic->id,
            'doctor_profile_id' => $doctor->id,
            'month' => '2026-11',
            'status' => 'completed',
            'payment_status' => 'paid',
        ]));

        $response->assertOk()
            ->assertViewIs('admin.dashboard')
            ->assertViewHas('totalAppointments', 1)
            ->assertViewHas('completedAppointments', 1)
            ->assertViewHas('revenue', '125.50')
            ->assertSee('grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4', false)
            ->assertSee('flex flex-col gap-2 sm:col-span-2 sm:flex-row xl:col-span-5', false);
    }
}
