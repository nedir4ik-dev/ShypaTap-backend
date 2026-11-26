<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($user->is_active, 403, 'Hasabyňyz işjeň däl.');
        if ($permission === 'auto') $permission = $this->resolvePermission($request);
        $clinicId = $request->input('clinic_id') ?? $request->query('clinic_id');
        if ($clinicId && !in_array(strtolower((string)$user->role), ['superadmin','super_admin'], true)) {
            abort_unless($user->belongsToClinic((int) $clinicId), 403, 'Bu klinika üçin rugsadyňyz ýok.');
        }
        abort_unless($user->hasPermission($permission, $clinicId ? (int) $clinicId : null), 403, 'Bu amal üçin rugsadyňyz ýok.');
        return $next($request);
    }

    private function resolvePermission(Request $request): string
    {
        $route = $request->route();
        $controller = class_basename((string) ($route?->getControllerClass() ?? ''));
        $action = (string) ($route?->getActionMethod() ?? 'index');
        $uri = (string) ($route?->uri() ?? '');
        $resource = match ($controller) {
            'AdminUserController' => 'users',
            'AdminDoctorController' => 'doctors',
            'AdminSpecializationController' => 'specializations',
            'AdminClinicController' => 'clinics',
            'AdminAppointmentController' => 'appointments',
            'AdminPaymentController' => 'payments',
            'AdminReviewController' => 'reviews',
            'AdminSettingController', 'SystemSettingController' => 'settings',
            'ProfileController' => 'profile',
            'AdminDashboardController', 'DashboardController', 'ReportPageController' => str_contains($uri, 'reports') ? 'reports' : 'dashboard',
            'TrashController' => 'trash',
            'RolePermissionController', 'RolePageController' => 'roles',
            default => 'dashboard',
        };
        if ($controller === 'AdminUserController' && $action === 'patients') return 'patients.view';
        if ($controller === 'RolePermissionController') return $action === 'overrides' ? 'permissions.assign' : 'roles.manage';
        if ($controller === 'ProfileController') return $action === 'edit' ? 'profile.view' : 'profile.update';
        if ($controller === 'RolePageController') return $action === 'assignOverrides' ? 'permissions.assign' : 'roles.manage';
        if ($controller === 'ReportPageController' && $action === 'export') return 'reports.export';
        if ($controller === 'TrashController') return match ($action) { 'restore' => 'trash.restore', 'forceDelete' => 'trash.force_delete', default => 'trash.view' };
        if (in_array($controller, ['AdminSettingController','SystemSettingController'], true)) return 'settings.manage';
        if ($controller === 'AdminAppointmentController' && $action === 'update' && in_array($request->input('status'), ['cancelled','rejected'], true)) return 'appointments.cancel';
        if ($controller === 'AdminPaymentController' && $action === 'update') {
            return match ($request->input('status')) { 'refunded' => 'payments.refund', 'paid' => 'payments.mark_paid', default => 'payments.update' };
        }
        return $resource.'.'.match ($action) {
            'index', 'show' => 'view',
            'create', 'store' => 'create',
            'edit', 'update', 'updateStatus', 'updateVerification', 'assignUserRoles' => 'update',
            'destroy' => 'delete',
            'forceDelete' => 'force_delete',
            'restore' => 'restore',
            default => 'view',
        };
    }
}
