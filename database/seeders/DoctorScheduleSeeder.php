<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\DoctorSchedule;
use App\Models\DoctorDayOff;

class DoctorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $clinic1 = Clinic::where('email', 'info@paytagtsaglyk.test')->firstOrFail();
        $clinic2 = Clinic::where('email', 'info@arassaklinika.test')->firstOrFail();

        $doctor1 = DoctorProfile::whereHas('user', fn($q) => $q->where('email', 'doctor1@sypatap.test'))->firstOrFail();
        $doctor2 = DoctorProfile::whereHas('user', fn($q) => $q->where('email', 'doctor2@sypatap.test'))->firstOrFail();
        $doctor3 = DoctorProfile::whereHas('user', fn($q) => $q->where('email', 'doctor3@sypatap.test'))->firstOrFail();

        $schedules = [
            [$clinic1->id, $doctor1->id, 1, '09:00', '13:00'],
            [$clinic2->id, $doctor1->id, 1, '14:00', '18:00'],
            [$clinic1->id, $doctor2->id, 1, '09:00', '17:00'],
            [$clinic2->id, $doctor3->id, 1, '10:00', '18:00'],
        ];

        foreach ($schedules as [$clinicId, $doctorId, $day, $start, $end]) {
            DoctorSchedule::updateOrCreate(
                [
                    'clinic_id' => $clinicId,
                    'doctor_profile_id' => $doctorId,
                    'day_of_week' => $day,
                    'start_time' => $start,
                ],
                [
                    'end_time' => $end,
                    'break_start' => ($start === '09:00' && $end === '17:00') || ($start === '10:00' && $end === '18:00') ? '13:00' : null,
                    'break_end' => ($start === '09:00' && $end === '17:00') || ($start === '10:00' && $end === '18:00') ? '14:00' : null,
                    'slot_duration_minutes' => 30,
                    'is_active' => true,
                ]
            );
        }

        DoctorDayOff::updateOrCreate(
            [
                'clinic_id' => $clinic1->id,
                'doctor_profile_id' => $doctor1->id,
                'off_date' => now()->addDays(14)->toDateString(),
            ],
            ['reason' => 'Meýilnamalaýyn dynç güni']
        );

        // DoctorSchedule::factory()->count(10)->create();
        // DoctorDayOff::factory()->count(3)->create();
    }
}
