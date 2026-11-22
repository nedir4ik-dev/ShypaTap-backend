<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (["doctor_profiles", "appointments", "reviews"] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, "deleted_at")) {
                Schema::table($tableName, fn (Blueprint $table) => $table->softDeletes());
            }
        }

        foreach (["users", "clinics", "doctor_profiles", "appointments", "reviews"] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, "trash_batch_id")) {
                Schema::table($tableName, fn (Blueprint $table) => $table->uuid("trash_batch_id")->nullable()->index());
            }
        }

        Schema::create("permissions", function (Blueprint $table) {
            $table->id(); $table->string("name", 120)->unique(); $table->string("group", 80)->nullable();
            $table->string("description", 255)->nullable(); $table->timestamps();
        });
        Schema::create("roles", function (Blueprint $table) {
            $table->id(); $table->string("name", 80)->unique(); $table->string("slug", 100)->unique();
            $table->boolean("is_system")->default(false); $table->timestamps();
        });
        Schema::create("permission_role", function (Blueprint $table) {
            $table->id(); $table->foreignId("role_id")->constrained()->cascadeOnDelete();
            $table->foreignId("permission_id")->constrained()->cascadeOnDelete(); $table->timestamps();
            $table->unique(["role_id", "permission_id"]);
        });
        Schema::create("user_roles", function (Blueprint $table) {
            $table->id(); $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("role_id")->constrained()->cascadeOnDelete();
            $table->foreignId("clinic_id")->nullable()->constrained()->cascadeOnDelete(); $table->timestamps();
            $table->unique(["user_id", "role_id", "clinic_id"]);
        });
        Schema::create("user_permission_overrides", function (Blueprint $table) {
            $table->id(); $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("permission_id")->constrained()->cascadeOnDelete();
            $table->foreignId("clinic_id")->nullable()->constrained()->cascadeOnDelete();
            $table->string("effect", 10); $table->timestamps();
            $table->unique(["user_id", "permission_id", "clinic_id"]);
        });
        Schema::create("role_clinic_scopes", function (Blueprint $table) {
            $table->id(); $table->foreignId("role_id")->constrained()->cascadeOnDelete();
            $table->foreignId("clinic_id")->constrained()->cascadeOnDelete(); $table->timestamps();
            $table->unique(["role_id", "clinic_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("role_clinic_scopes"); Schema::dropIfExists("user_permission_overrides");
        Schema::dropIfExists("user_roles"); Schema::dropIfExists("permission_role");
        Schema::dropIfExists("roles"); Schema::dropIfExists("permissions");
        foreach (["users", "clinics", "doctor_profiles", "appointments", "reviews"] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, "trash_batch_id")) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn("trash_batch_id"));
            }
        }
        foreach (["doctor_profiles", "appointments", "reviews"] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, "deleted_at")) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropSoftDeletes());
            }
        }
    }
};
