<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\Specialization;

class DoctorProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'email' => 'doctor1@sypatap.test',
                'specialization' => 'kardiolog',
                'experience_years' => 12,
                'education' => 'Lukmançylyk uniwersiteti, kardiologiýa hünäri.',
                'biography' => 'Ýürek-damar kesellerini anyklamak we bejermek boýunça tejribeli lukman.',
                'consultation_fee' => 250,
            ],
            [
                'email' => 'doctor2@sypatap.test',
                'specialization' => 'stomatolog',
                'experience_years' => 9,
                'education' => 'Lukmançylyk uniwersiteti, stomatologiýa fakulteti.',
                'biography' => 'Diş bejergisi we öňüni alyş hyzmatlary boýunça hünärmen.',
                'consultation_fee' => 180,
            ],
            [
                'email' => 'doctor3@sypatap.test',
                'specialization' => 'pediatr',
                'experience_years' => 15,
                'education' => 'Çagalar lukmançylygy boýunça ýokary okuw.',
                'biography' => 'Çagalaryň ösüşine we saglygyna gözegçilik edýär.',
                'consultation_fee' => 200,
            ],
        ];

        foreach ($profiles as $item) {
            $user = User::where('email', $item['email'])->firstOrFail();
            $specialization = Specialization::where('slug', $item['specialization'])->firstOrFail();

            DoctorProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'specialization_id' => $specialization->id,
                    'experience_years' => $item['experience_years'],
                    'education' => $item['education'],
                    'biography' => $item['biography'],
                    'consultation_fee' => $item['consultation_fee'],
                    'currency' => 'TMT',
                    'is_verified' => true,
                ]
            );
        }
        
        // DoctorProfile::factory()->count(5)->create();
    }
}
