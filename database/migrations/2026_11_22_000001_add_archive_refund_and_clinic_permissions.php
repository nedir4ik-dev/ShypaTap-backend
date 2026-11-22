<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reviews') && ! Schema::hasColumn('reviews', 'deleted_at')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('payments') && ! Schema::hasColumn('payments', 'refunded_at')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->timestamp('refunded_at')->nullable();
            });
        }

        if (Schema::hasTable('clinic_staff') && ! Schema::hasColumn('clinic_staff', 'permissions')) {
            Schema::table('clinic_staff', function (Blueprint $table) {
                $table->json('permissions')->nullable();
            });
        }

        if (Schema::hasTable('clinic_doctors') && ! Schema::hasColumn('clinic_doctors', 'permissions')) {
            Schema::table('clinic_doctors', function (Blueprint $table) {
                $table->json('permissions')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clinic_doctors') && Schema::hasColumn('clinic_doctors', 'permissions')) {
            Schema::table('clinic_doctors', fn (Blueprint $table) => $table->dropColumn('permissions'));
        }
        if (Schema::hasTable('clinic_staff') && Schema::hasColumn('clinic_staff', 'permissions')) {
            Schema::table('clinic_staff', fn (Blueprint $table) => $table->dropColumn('permissions'));
        }
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'refunded_at')) {
            Schema::table('payments', fn (Blueprint $table) => $table->dropColumn('refunded_at'));
        }
        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'deleted_at')) {
            Schema::table('reviews', fn (Blueprint $table) => $table->dropSoftDeletes());
        }
    }
};
