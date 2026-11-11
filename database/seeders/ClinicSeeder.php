<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        $clinics = [
            [
                'name' => 'Paýtagt Saglyk Merkezi',
                'phone' => '+993 12 45-10-10',
                'email' => 'info@paytagtsaglyk.test',
                'city' => 'Aşgabat',
                'address' => 'Aşgabat şäheri, Magtymguly şaýoly',
                'description' => 'Köpugurly lukmançylyk we anyklaýyş merkezi.',
                'timezone' => 'Asia/Ashgabat',
                'is_active' => true,
            ],
            [
                'name' => 'Arassaçylyk Klinikasy',
                'phone' => '+993 12 45-20-20',
                'email' => 'info@arassaklinika.test',
                'city' => 'Aşgabat',
                'address' => 'Aşgabat şäheri, Bitarap Türkmenistan şaýoly',
                'description' => 'Maşgala lukmançylygy we ýöriteleşdirilen hyzmatlar.',
                'timezone' => 'Asia/Ashgabat',
                'is_active' => true,
            ],
        ];

        foreach ($clinics as $clinic) {
            Clinic::updateOrCreate(['email' => $clinic['email']], $clinic);
        }

        // Clinic::factory()->count(3)->create();
    }
}
