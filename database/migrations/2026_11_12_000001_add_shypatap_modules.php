<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('patient_phone', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('type', 50)->nullable();
            $table->jsonb('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('TMT');
            $table->string('status', 20)->default('pending');
            $table->string('payment_method', 30)->default('cash');
            $table->timestamp('paid_at')->nullable();
            $table->string('provider_reference')->nullable();
            $table->timestamps();
            $table->index(['status', 'paid_at']);
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        DB::table('settings')->insert([
            ['key' => 'timezone', 'value' => 'Asia/Ashgabat', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'currency', 'value' => 'TMT', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'cancellation_hours', 'value' => '12', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_slot_duration', 'value' => '30', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'system_name', 'value' => 'ŞypaTap', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('notifications');
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['patient_phone', 'notes', 'reminder_sent_at']);
        });
    }
};
