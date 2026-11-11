<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->foreignId('specialization_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->text('education')->nullable();
            $table->text('biography')->nullable();
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->char('currency', 3)->default('TMT');
            $table->string('photo_path')->nullable();
            $table->boolean('is_verified')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
