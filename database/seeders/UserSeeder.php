<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'ŞypaTap Admin', 'email' => 'admin@sypatap.test', 'phone' => '+99361000001', 'role' => 'admin'],
            ['name' => 'Dr. Aýlar Mämmedowa', 'email' => 'doctor1@sypatap.test', 'phone' => '+99361000002', 'role' => 'doctor'],
            ['name' => 'Dr. Myrat Annageldiýew', 'email' => 'doctor2@sypatap.test', 'phone' => '+99361000003', 'role' => 'doctor'],
            ['name' => 'Dr. Selbi Orazowa', 'email' => 'doctor3@sypatap.test', 'phone' => '+99361000004', 'role' => 'doctor'],
            ['name' => 'Aýna Berdiýewa', 'email' => 'patient1@sypatap.test', 'phone' => '+99361000005', 'role' => 'patient'],
            ['name' => 'Merdan Hudaýberdiýew', 'email' => 'patient2@sypatap.test', 'phone' => '+99361000006', 'role' => 'patient'],
            ['name' => 'Oguljahan Saparowa', 'email' => 'patient3@sypatap.test', 'phone' => '+99361000007', 'role' => 'patient'],
            ['name' => 'Döwlet Amanow', 'email' => 'patient4@sypatap.test', 'phone' => '+99361000008', 'role' => 'patient'],
            ['name' => 'Mähri Rejepowa', 'email' => 'reception1@sypatap.test', 'phone' => '+99361000009', 'role' => 'receptionist'],
            ['name' => 'Azat Baýramow', 'email' => 'reception2@sypatap.test', 'phone' => '+99361000010', 'role' => 'receptionist'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data + [
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
        // User::factory()->patient()->count(10)->create();
    }
}
