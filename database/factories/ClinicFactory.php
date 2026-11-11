<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Clinic;

/** @extends Factory<Clinic> */
class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' Medical Center',
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'city' => 'Ashgabat',
            'address' => fake()->streetAddress(),
            'description' => fake()->paragraph(),
            'timezone' => 'Asia/Ashgabat',
            'is_active' => true,
        ];
    }
}
