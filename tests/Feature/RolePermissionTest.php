<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Http\Middleware\PermissionMiddleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_explicit_user_deny_overrides_a_role_permission_grant(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'appointments.view'], ['group' => 'appointments']);
        $role = Role::create(['name' => 'Clinic Reader', 'slug' => 'clinic-reader', 'is_system' => false]);
        $role->permissions()->attach($permission->id);
        $user = User::factory()->create(['role' => 'patient']);
        $user->roles()->attach($role->id, ['clinic_id' => null]);

        $this->assertTrue($user->hasPermission('appointments.view'));
        $user->permissionOverrides()->create(['permission_id' => $permission->id, 'effect' => 'deny']);
        $this->assertFalse($user->fresh()->hasPermission('appointments.view'));
    }

    public function test_explicit_user_allow_can_add_a_permission(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'reports.view'], ['group' => 'reports']);
        $user = User::factory()->create(['role' => 'patient']);
        $this->assertFalse($user->hasPermission('reports.view'));

        $user->permissionOverrides()->create(['permission_id' => $permission->id, 'effect' => 'allow']);
        $this->assertTrue($user->fresh()->hasPermission('reports.view'));
    }

    public function test_superadmin_has_all_permissions_without_role_assignment(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->assertTrue($user->hasPermission('any.future.permission'));
        $this->assertTrue($user->belongsToClinic(999999));
    }

    public function test_receptionist_payment_update_route_is_not_exposed(): void
    {
        $user = User::factory()->receptionist()->create();
        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/receptionist/payments/1', ['status' => 'paid'])
            ->assertNotFound();
    }

    public function test_patient_permission_is_not_blocked_by_clinic_membership_scope(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'appointments.create'], ['group' => 'appointments']);
        $role = Role::firstOrCreate(['slug' => 'patient'], ['name' => 'Patient', 'is_system' => true]);
        $role->permissions()->syncWithoutDetaching([$permission->id]);
        $user = User::factory()->create(['role' => 'patient']);

        $request = \Illuminate\Http\Request::create('/api/appointments', 'POST', ['clinic_id' => 99999]);
        $request->setUserResolver(fn() => $user);
        $response = app(PermissionMiddleware::class)->handle(
            $request,
            fn() => response()->json(['ok' => true]),
            'appointments.create'
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_receptionist_still_requires_membership_in_selected_clinic(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'appointments.create'], ['group' => 'appointments']);
        $role = Role::firstOrCreate(['slug' => 'receptionist'], ['name' => 'Receptionist', 'is_system' => true]);
        $role->permissions()->syncWithoutDetaching([$permission->id]);
        $user = User::factory()->create(['role' => 'receptionist']);

        $request = \Illuminate\Http\Request::create('/api/receptionist/appointments', 'POST', ['clinic_id' => 99999]);
        $request->setUserResolver(fn() => $user);

        $this->expectException(HttpException::class);
        app(PermissionMiddleware::class)->handle(
            $request,
            fn() => response()->json(['ok' => true]),
            'appointments.create'
        );
    }
}
