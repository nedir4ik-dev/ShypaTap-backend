<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\SpecializationController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminClinicController;
use App\Http\Controllers\Api\Admin\AdminSpecializationController;
use App\Http\Controllers\Api\Admin\AdminDoctorController;
use App\Http\Controllers\Api\Admin\AdminAppointmentController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\ReceptionistController;
use App\Http\Controllers\Api\DoctorScheduleController;
use App\Http\Controllers\Api\Admin\AdminPaymentController;
use App\Http\Controllers\Api\Admin\AdminSettingController;
use App\Http\Controllers\Api\Admin\AdminReviewController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserSettingController;
use App\Http\Controllers\Api\Admin\TrashController;
use App\Http\Controllers\Api\Admin\RolePermissionController;


Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});
Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}/slots', [DoctorController::class, 'slots']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);
Route::get('/specializations', [SpecializationController::class, 'index']);
Route::get('/clinics', [ClinicController::class, 'index']);

Route::middleware(['auth:sanctum', 'role:patient,doctor,receptionist,admin,superadmin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me'])->middleware('permission:profile.view');
    Route::patch('/profile', [AuthController::class, 'updateProfile'])->middleware('permission:profile.update');
    Route::post('/profile/avatar', [AuthController::class, 'uploadAvatar'])->middleware('permission:profile.update');
    Route::delete('/profile/avatar', [AuthController::class, 'deleteAvatar'])->middleware('permission:profile.update');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('permission:profile.update');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'read']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::get('/settings/me', [UserSettingController::class, 'show'])->middleware('permission:profile.view');
    Route::patch('/settings/me', [UserSettingController::class, 'update'])->middleware('permission:profile.update');
});
Route::middleware(['auth:sanctum', 'role:patient'])->group(function () {
    Route::post('/appointments', [AppointmentController::class, 'store'])->middleware('permission:appointments.create');
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments'])->middleware('permission:appointments.view');
    Route::patch('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->middleware('permission:appointments.cancel');
    Route::post('/appointments/{id}/review', [AppointmentController::class, 'storeReview'])->middleware('permission:reviews.create');
    Route::patch('/reviews/{id}', [AppointmentController::class, 'updateReview'])->middleware('permission:reviews.update');
    Route::delete('/reviews/{id}', [AppointmentController::class, 'deleteReview'])->middleware('permission:reviews.delete.own');
});
Route::middleware(['auth:sanctum', 'role:doctor'])->prefix('doctor')->group(function () {
    Route::get('/dashboard', [AppointmentController::class, 'doctorDashboard'])->middleware('permission:dashboard.view');
    Route::get('/profile', [DoctorController::class, 'getProfile'])->middleware('permission:profile.view');
    Route::patch('/profile', [DoctorController::class, 'updateProfile'])->middleware('permission:profile.update');
    Route::get('/appointments', [AppointmentController::class, 'doctorAppointments'])->middleware('permission:appointments.view');
    Route::get('/calendar', [AppointmentController::class, 'doctorCalendar'])->middleware('permission:appointments.view');
    Route::get('/schedules', [DoctorScheduleController::class, 'index'])->middleware('permission:schedules.view');
    Route::post('/schedules', [DoctorScheduleController::class, 'store'])->middleware('permission:schedules.manage');
    Route::put('/schedules/{id}', [DoctorScheduleController::class, 'update'])->middleware('permission:schedules.manage');
    Route::delete('/schedules/{id}', [DoctorScheduleController::class, 'destroy'])->middleware('permission:schedules.manage');
    Route::get('/days-off', [DoctorScheduleController::class, 'daysOff'])->middleware('permission:schedules.view');
    Route::post('/days-off', [DoctorScheduleController::class, 'addDayOff'])->middleware('permission:days_off.manage');
    Route::delete('/days-off/{id}', [DoctorScheduleController::class, 'deleteDayOff'])->middleware('permission:days_off.manage');
    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])->middleware('permission:appointments.status.update');
    Route::get('/appointments/{id}', [AppointmentController::class, 'doctorAppointmentShow'])->middleware('permission:appointments.view');
    Route::get('/reviews', [AppointmentController::class, 'doctorReviews'])->middleware('permission:reviews.view');
});
Route::middleware(['auth:sanctum', 'role:receptionist,admin,superadmin'])->prefix('receptionist')->group(function () {
    Route::get('/dashboard', [ReceptionistController::class, 'dashboard'])->middleware('permission:dashboard.view');
    Route::get('/appointments', [ReceptionistController::class, 'appointments'])->middleware('permission:appointments.view');
    Route::post('/appointments', [AppointmentController::class, 'storeForPatient'])->middleware('permission:appointments.create');
    Route::get('/patients', [ReceptionistController::class, 'patients'])->middleware('permission:patients.view');
    Route::get('/doctors', [ReceptionistController::class, 'doctors'])->middleware('permission:doctors.view');
    Route::get('/payments', [PaymentController::class, 'receptionistIndex'])->middleware('permission:payments.view');
});
Route::middleware(['auth:sanctum', 'role:admin,superadmin', 'permission:auto'])->prefix('admin')->group(function () {
    Route::get('/trash', [TrashController::class, 'index'])->middleware('permission:trash.view');
    Route::post('/trash/{type}/{id}/restore', [TrashController::class, 'restore'])->middleware('permission:trash.restore');
    Route::delete('/trash/{type}/{id}', [TrashController::class, 'forceDelete'])->middleware('permission:trash.force_delete');
    Route::get('/roles', [RolePermissionController::class, 'index'])->middleware('permission:roles.manage');
    Route::post('/roles', [RolePermissionController::class, 'store'])->middleware('permission:roles.manage');
    Route::put('/roles/{role}', [RolePermissionController::class, 'update'])->middleware('permission:roles.manage');
    Route::delete('/roles/{role}', [RolePermissionController::class, 'destroy'])->middleware('permission:roles.manage');
    Route::put('/users/{user}/roles', [RolePermissionController::class, 'assignUserRoles'])->middleware('permission:roles.manage');
    Route::put('/users/{user}/permission-overrides', [RolePermissionController::class, 'overrides'])->middleware('permission:permissions.assign');
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::apiResource('users', AdminUserController::class);
    Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus']);
    Route::apiResource('clinics', AdminClinicController::class);
    Route::apiResource('specializations', AdminSpecializationController::class);
    Route::apiResource('doctors', AdminDoctorController::class);
    Route::patch('/doctors/{doctor}/verify', [AdminDoctorController::class, 'updateVerification']);
    Route::apiResource('appointments', AdminAppointmentController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::apiResource('payments', AdminPaymentController::class)->only(['index', 'show', 'update']);
    Route::get('/settings', [AdminSettingController::class, 'index']);
    Route::patch('/settings', [AdminSettingController::class, 'update']);
    Route::get('/reports', [AdminDashboardController::class, 'index']);
    Route::apiResource('reviews', AdminReviewController::class)->only(['index', 'destroy']);
});
