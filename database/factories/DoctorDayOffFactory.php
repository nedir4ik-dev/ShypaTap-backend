<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DoctorDayOff;
use App\Models\Clinic;
use App\Models\DoctorProfile;

/** @extends Factory<DoctorDayOff> */
class DoctorDayOffFactory extends Factory
{
    protected $model = DoctorDayOff::class;

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'doctor_profile_id' => DoctorProfile::factory(),
            'off_date' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'reason' => fake()->randomElement(['Annual leave', 'Training', 'Personal day']),
        ];
    }
}
