<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Appointment;
use App\Models\User;
use App\Models\DoctorProfile;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $patient = User::where('email', 'patient1@sypatap.test')->firstOrFail();
        $doctor = DoctorProfile::whereHas('user', fn ($q) => $q->where('email', 'doctor1@sypatap.test'))->firstOrFail();

        // Review diňe completed appointment üçin döredilmelidir.
        // Demo üçin öňki senedäki tamamlanan appointment döredilýär.
        $appointment = Appointment::updateOrCreate(
            ['booking_code' => 'ST-DEMO-REVIEW-0001'],
            [
                'patient_id' => $patient->id,
                'doctor_profile_id' => $doctor->id,
                'clinic_id' => $doctor->clinics()->firstOrFail()->id,
                'appointment_date' => now()->subDays(2)->toDateString(),
                'start_time' => '11:00',
                'end_time' => '11:30',
                'duration_minutes' => 30,
                'status' => 'completed',
                'reason' => 'Öňüni alyş barlagy.',
                'completed_at' => now()->subDays(2),
            ]
        );

        Review::updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'patient_id' => $patient->id,
                'doctor_profile_id' => $doctor->id,
                'rating' => 5,
                'comment' => 'Lukman düşündirişleri düşnükli berdi, hyzmatdan razy galdym.',
            ]
        );

        // Review::factory()->count(10)->create();
    }
}
