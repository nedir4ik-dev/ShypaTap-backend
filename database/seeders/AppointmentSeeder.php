<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Clinic;
use App\Models\DoctorProfile;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $clinic1 = Clinic::where('email', 'info@paytagtsaglyk.test')->firstOrFail();
        $doctor1 = DoctorProfile::whereHas('user', fn ($q) => $q->where('email', 'doctor1@sypatap.test'))->firstOrFail();
        $doctor2 = DoctorProfile::whereHas('user', fn ($q) => $q->where('email', 'doctor2@sypatap.test'))->firstOrFail();

        $patient1 = User::where('email', 'patient1@sypatap.test')->firstOrFail();
        $patient2 = User::where('email', 'patient2@sypatap.test')->firstOrFail();
        $reception = User::where('email', 'reception1@sypatap.test')->firstOrFail();

        $date = now()->addDays(8)->toDateString();

        Appointment::updateOrCreate(
            ['booking_code' => 'ST-DEMO-0001'],
            [
                'patient_id' => $patient1->id,
                'doctor_profile_id' => $doctor1->id,
                'clinic_id' => $clinic1->id,
                'created_by_user_id' => null,
                'appointment_date' => $date,
                'start_time' => '09:00',
                'end_time' => '09:30',
                'duration_minutes' => 30,
                'status' => 'confirmed',
                'reason' => 'Ýürek barlagy üçin maslahat.',
                'confirmed_at' => now(),
            ]
        );

        Appointment::updateOrCreate(
            ['booking_code' => 'ST-DEMO-0002'],
            [
                'patient_id' => $patient2->id,
                'doctor_profile_id' => $doctor2->id,
                'clinic_id' => $clinic1->id,
                'created_by_user_id' => $reception->id,
                'appointment_date' => $date,
                'start_time' => '10:00',
                'end_time' => '10:30',
                'duration_minutes' => 30,
                'status' => 'pending',
                'reason' => 'Diş lukmanynyň maslahatyny almak.',
            ]
        );

        // Appointment::factory()->count(20)->create();
    }
}
