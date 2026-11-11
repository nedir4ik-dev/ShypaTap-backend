<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Appointment;
use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\Clinic;

/** @extends Factory<Appointment> */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $start = fake()->randomElement(['09:00', '09:30', '10:00', '10:30', '14:00', '14:30']);

        return [
            'booking_code' => 'ST-'.Str::upper(Str::random(10)),
            'patient_id' => User::factory()->patient(),
            'doctor_profile_id' => DoctorProfile::factory(),
            'clinic_id' => Clinic::factory(),
            'created_by_user_id' => null,
            'appointment_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'start_time' => $start,
            'end_time' => date('H:i:s', strtotime($start.' +30 minutes')),
            'duration_minutes' => 30,
            'status' => 'pending',
            'reason' => fake()->sentence(),
            'cancellation_reason' => null,
        ];
    }
}
