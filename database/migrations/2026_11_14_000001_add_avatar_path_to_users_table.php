<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('phone');
        });

        DB::table('doctor_profiles')
            ->whereNotNull('photo_path')
            ->where('photo_path', '<>', '')
            ->orderBy('id')
            ->chunk(200, function ($doctors) {
                foreach ($doctors as $doctor) {
                    DB::table('users')
                        ->where('id', $doctor->user_id)
                        ->whereNull('avatar_path')
                        ->update(['avatar_path' => $doctor->photo_path]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_path');
        });
    }
};
