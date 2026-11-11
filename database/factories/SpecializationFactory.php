<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Specialization;

/** @extends Factory<Specialization> */
class SpecializationFactory extends Factory
{
    protected $model = Specialization::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Cardiology', 'Dentistry', 'Pediatrics', 'Neurology',
            'Dermatology', 'Ophthalmology',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
