<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void {
  if (DB::connection()->getDriverName() !== 'pgsql') return;
  try {
   DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
   DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_no_overlapping_active EXCLUDE USING gist (doctor_profile_id WITH =, tsrange(appointment_date + start_time, appointment_date + end_time, '[)') WITH &&) WHERE (status IN ('pending','confirmed'))");
  } catch (\Throwable $e) {
   // DB-niň giňeltme rugsady ýok bolsa, service derejesindäki row-locking goragy dowam edýär.
  }
 }
 public function down(): void {
  if (DB::connection()->getDriverName() === 'pgsql' && Schema::hasTable('appointments')) {
   DB::statement('ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_no_overlapping_active');
  }
 }
};
