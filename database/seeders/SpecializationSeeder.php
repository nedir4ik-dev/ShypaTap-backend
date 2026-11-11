<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialization;

class SpecializationSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Kardiolog', 'slug' => 'kardiolog', 'description' => 'Ýürek we gan-damar keselleri.'],
            ['name' => 'Stomatolog', 'slug' => 'stomatolog', 'description' => 'Diş we agyz boşlugynyň saglygy.'],
            ['name' => 'Pediatr', 'slug' => 'pediatr', 'description' => 'Çagalaryň saglygy we bejergisi.'],
            ['name' => 'Newrolog', 'slug' => 'newrolog', 'description' => 'Nerw ulgamy keselleri.'],
            ['name' => 'Dermatolog', 'slug' => 'dermatolog', 'description' => 'Deri keselleri.'],
        ];

        foreach ($items as $item) {
            Specialization::updateOrCreate(
                ['slug' => $item['slug']],
                $item + ['is_active' => true]
            );
        }

        // Specialization::factory()->count(5)->create();
    }
}
