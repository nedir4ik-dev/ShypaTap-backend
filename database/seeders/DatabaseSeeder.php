<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SpecializationSeeder::class,
            ClinicSeeder::class,
            UserSeeder::class,
            DoctorProfileSeeder::class,
            ClinicRelationSeeder::class,
            DoctorScheduleSeeder::class,
            AppointmentSeeder::class,
            ReviewSeeder::class,
        ]);

        // \App\Models\User::factory()->patient()->count(20)->create();
        // \App\Models\Clinic::factory()->count(5)->create();

    }
}
