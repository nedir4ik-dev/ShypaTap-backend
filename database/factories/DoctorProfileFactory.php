<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DoctorProfile;
use App\Models\User;
use App\Models\Specialization;

/** @extends Factory<DoctorProfile> */
class DoctorProfileFactory extends Factory
{
    protected $model = DoctorProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->doctor(),
            'specialization_id' => Specialization::factory(),
            'experience_years' => fake()->numberBetween(1, 25),
            'education' => fake()->sentence(),
            'biography' => fake()->paragraph(),
            'consultation_fee' => fake()->randomFloat(2, 100, 500),
            'currency' => 'TMT',
            'photo_path' => null,
            'is_verified' => true,
        ];
    }
}
