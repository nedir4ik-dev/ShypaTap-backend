<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'date_of_birth')) {
            Schema::table('users', fn(Blueprint $table) => $table->date('date_of_birth')->nullable());
        }
        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'refunded_at')) {
            Schema::table('payments', fn(Blueprint $table) => $table->timestamp('refunded_at')->nullable());
        }
        if (Schema::hasTable('payment_histories')) {
            Schema::table('payment_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_histories', 'amount')) $table->decimal('amount', 10, 2)->nullable();
                if (!Schema::hasColumn('payment_histories', 'reason')) $table->string('reason', 500)->nullable();
            });
        }
        $permissions = [
            'dashboard.view',
            'profile.view',
            'profile.update',
            'schedules.view',
            'schedules.manage',
            'days_off.manage',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'doctors.view',
            'doctors.create',
            'doctors.update',
            'doctors.delete',
            'specializations.view',
            'specializations.create',
            'specializations.update',
            'specializations.delete',
            'clinics.view',
            'clinics.create',
            'clinics.update',
            'clinics.delete',
            'appointments.view',
            'appointments.create',
            'appointments.update',
            'appointments.delete',
            'appointments.cancel',
            'appointments.status.update',
            'patients.view',
            'reviews.view',
            'reviews.create',
            'reviews.update',
            'reviews.delete',
            'reviews.delete.own',
            'payments.view',
            'payments.mark_paid',
            'payments.refund',
            'payments.update',
            'reports.view',
            'reports.export',
            'roles.manage',
            'permissions.assign',
            'trash.view',
            'trash.restore',
            'trash.force_delete',
            'settings.manage',
        ];
        foreach ($permissions as $name) {
            DB::table('permissions')->updateOrInsert(['name' => $name], [
                'group' => explode('.', $name)[0],
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        foreach ([['SuperAdmin', 'superadmin'], ['Admin', 'admin'], ['Doctor', 'doctor'], ['Receptionist', 'receptionist'], ['Patient', 'patient']] as [$name, $slug]) {
            DB::table('roles')->updateOrInsert(['slug' => $slug], [
                'name' => $name,
                'is_system' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
        $all = DB::table('permissions')->pluck('id', 'name');
        $super = DB::table('roles')->where('slug', 'superadmin')->value('id');
        foreach ($all as $permissionId) {
            DB::table('permission_role')->updateOrInsert(['role_id' => $super, 'permission_id' => $permissionId], ['created_at' => now(), 'updated_at' => now()]);
        }
        $defaults = [
            'admin' => $permissions,
            'doctor' => ['dashboard.view', 'profile.view', 'profile.update', 'schedules.view', 'schedules.manage', 'days_off.manage', 'appointments.view', 'appointments.status.update', 'patients.view', 'reviews.view', 'payments.view'],
            'receptionist' => ['dashboard.view', 'profile.view', 'profile.update', 'appointments.view', 'appointments.create', 'patients.view', 'doctors.view', 'payments.view', 'clinics.view'],
            'patient' => ['profile.view', 'profile.update', 'appointments.view', 'appointments.create', 'reviews.create', 'reviews.update', 'reviews.delete.own'],
        ];
        foreach ($defaults as $slug => $names) {
            $roleId = DB::table('roles')->where('slug', $slug)->value('id');
            foreach ($names as $name) {
                if (isset($all[$name])) DB::table('permission_role')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $all[$name]],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
        foreach (DB::table('users')->whereNull('deleted_at')->get(['id', 'role']) as $user) {
            $slug = strtolower((string)$user->role);
            if ($slug === 'super_admin') $slug = 'superadmin';
            $roleId = DB::table('roles')->where('slug', $slug)->value('id');
            if ($roleId) DB::table('user_roles')->updateOrInsert(
                ['user_id' => $user->id, 'role_id' => $roleId, 'clinic_id' => null],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payments', 'refunded_at')) Schema::table('payments', fn(Blueprint $table) => $table->dropColumn('refunded_at'));
    }
};
