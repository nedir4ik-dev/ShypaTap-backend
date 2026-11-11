<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_days_off', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->restrictOnDelete();
            $table->foreignId('doctor_profile_id')->constrained()->restrictOnDelete();
            $table->date('off_date');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['clinic_id', 'doctor_profile_id', 'off_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_days_off');
    }
};
