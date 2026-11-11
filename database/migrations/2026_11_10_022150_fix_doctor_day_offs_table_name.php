<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('doctor_day_offs')) {
            return;
        }

        if (Schema::hasTable('doctor_days_off')) {
            Schema::rename('doctor_days_off', 'doctor_day_offs');
            return;
        }

        if (Schema::hasTable('doctor_days_offs')) {
            Schema::rename('doctor_days_offs', 'doctor_day_offs');
            return;
        }
        Schema::create('doctor_day_offs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->restrictOnDelete();
            $table->foreignId('doctor_profile_id')->constrained('doctor_profiles')->restrictOnDelete();
            $table->date('off_date');
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->unique(
                ['clinic_id', 'doctor_profile_id', 'off_date'],
                'doctor_day_offs_unique'
            );
        });
    }

    public function down(): void
    {
        // Maglumatlary ýitirmezlik üçin bu düzediş migration-y
        // tablisany pozmaýar we öňki adyna gaýtarmaýar.
    }
};
