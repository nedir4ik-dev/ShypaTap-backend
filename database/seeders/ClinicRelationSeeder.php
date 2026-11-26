<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use App\Models\User;
use App\Models\DoctorProfile;

class ClinicRelationSeeder extends Seeder
{
    public function run(): void
    {
        $clinic1 = Clinic::where('email', 'info@paytagtsaglyk.test')->firstOrFail();
        $clinic2 = Clinic::where('email', 'info@arassaklinika.test')->firstOrFail();

        $doctor1 = DoctorProfile::whereHas('user', fn ($q) => $q->where('email', 'doctor1@sypatap.test'))->firstOrFail();
        $doctor2 = DoctorProfile::whereHas('user', fn ($q) => $q->where('email', 'doctor2@sypatap.test'))->firstOrFail();
        $doctor3 = DoctorProfile::whereHas('user', fn ($q) => $q->where('email', 'doctor3@sypatap.test'))->firstOrFail();

        $clinic1->doctors()->syncWithoutDetaching([$doctor1->id, $doctor2->id]);
        $clinic2->doctors()->syncWithoutDetaching([$doctor1->id, $doctor3->id]);

        $admin = User::where('email','admin@sypatap.test')->firstOrFail();
        $clinic1->staff()->syncWithoutDetaching([$admin->id]);
        $clinic2->staff()->syncWithoutDetaching([$admin->id]);

        $reception1 = User::where('email', 'reception1@sypatap.test')->firstOrFail();
        $reception2 = User::where('email', 'reception2@sypatap.test')->firstOrFail();

        $clinic1->staff()->syncWithoutDetaching([$reception1->id]);
        $clinic2->staff()->syncWithoutDetaching([$reception2->id]);
    }
}
