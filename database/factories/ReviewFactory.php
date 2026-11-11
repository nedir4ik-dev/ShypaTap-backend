<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Review;
use App\Models\Appointment;
use App\Models\User;
use App\Models\DoctorProfile;

/** @extends Factory<Review> */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'patient_id' => User::factory()->patient(),
            'doctor_profile_id' => DoctorProfile::factory(),
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->sentence(),
        ];
    }
}
