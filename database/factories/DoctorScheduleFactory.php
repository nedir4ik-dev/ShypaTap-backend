<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DoctorSchedule;
use App\Models\Clinic;
use App\Models\DoctorProfile;

/** @extends Factory<DoctorSchedule> */
class DoctorScheduleFactory extends Factory
{
    protected $model = DoctorSchedule::class;

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'doctor_profile_id' => DoctorProfile::factory(),
            'day_of_week' => fake()->numberBetween(1, 7),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'break_start' => '13:00:00',
            'break_end' => '14:00:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ];
    }
}
