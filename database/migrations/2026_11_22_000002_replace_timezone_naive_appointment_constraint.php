<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Appointment wall-clock values are interpreted in each clinic's timezone.
        // A timestamp-without-time-zone exclusion index would compare different
        // local timezones as if they were the same instant, so conflict checks are
        // performed transactionally after locking the doctor profile row.
        if (DB::connection()->getDriverName() === 'pgsql' && Schema::hasTable('appointments')) {
            DB::statement('ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_no_overlapping_active');
        }
    }

    public function down(): void
    {
        // Intentionally do not recreate the timezone-naive constraint.
        // Recreating it could reject valid bookings between clinics in different timezones.
    }
};
