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


Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});
Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}/slots', [DoctorController::class, 'slots']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);
Route::get('/specializations', [SpecializationController::class, 'index']);
Route::get('/clinics', [ClinicController::class, 'index']);

Route::middleware(['auth:sanctum', 'role:patient,doctor,receptionist,admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/avatar', [AuthController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [AuthController::class, 'deleteAvatar']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'read']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::get('/settings/me', [UserSettingController::class, 'show']);
    Route::patch('/settings/me', [UserSettingController::class, 'update']);
});
Route::middleware(['auth:sanctum', 'role:patient'])->group(function () {
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']);
    Route::patch('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/appointments/{id}/review', [AppointmentController::class, 'storeReview']);
    Route::delete('/reviews/{id}', [AppointmentController::class, 'deleteReview']);
});
Route::middleware(['auth:sanctum', 'role:doctor'])->prefix('doctor')->group(function () {
    Route::get('/dashboard', [AppointmentController::class, 'doctorDashboard'])->middleware('clinic.permission:dashboard.view');
    Route::get('/profile', [DoctorController::class, 'getProfile']);
    Route::patch('/profile', [DoctorController::class, 'updateProfile']);
    Route::get('/appointments', [AppointmentController::class, 'doctorAppointments'])->middleware('clinic.permission:appointments.view');
    Route::get('/calendar', [AppointmentController::class, 'doctorCalendar'])->middleware('clinic.permission:appointments.view');
    Route::get('/schedules', [DoctorScheduleController::class, 'index'])->middleware('clinic.permission:schedules.view');
    Route::post('/schedules', [DoctorScheduleController::class, 'store'])->middleware('clinic.permission:schedules.manage');
    Route::put('/schedules/{id}', [DoctorScheduleController::class, 'update'])->middleware('clinic.permission:schedules.manage');
    Route::delete('/schedules/{id}', [DoctorScheduleController::class, 'destroy'])->middleware('clinic.permission:schedules.manage');
    Route::get('/days-off', [DoctorScheduleController::class, 'daysOff'])->middleware('clinic.permission:schedules.view');
    Route::post('/days-off', [DoctorScheduleController::class, 'addDayOff'])->middleware('clinic.permission:schedules.manage');
    Route::delete('/days-off/{id}', [DoctorScheduleController::class, 'deleteDayOff'])->middleware('clinic.permission:schedules.manage');
    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])->middleware('clinic.permission:appointments.manage');
    Route::get('/appointments/{id}', [AppointmentController::class, 'doctorAppointmentShow'])->middleware('clinic.permission:appointments.view');
    Route::get('/reviews', [AppointmentController::class, 'doctorReviews'])->middleware('clinic.permission:reviews.view');
    Route::patch('/payments/{payment}', [PaymentController::class, 'update'])->middleware('clinic.permission:payments.manage');
});
Route::middleware(['auth:sanctum', 'role:receptionist,admin'])->prefix('receptionist')->group(function () {
    Route::get('/dashboard', [ReceptionistController::class, 'dashboard'])->middleware('clinic.permission:dashboard.view');
    Route::get('/appointments', [ReceptionistController::class, 'appointments'])->middleware('clinic.permission:appointments.view');
    Route::post('/appointments', [AppointmentController::class, 'storeForPatient'])->middleware('clinic.permission:appointments.create');
    Route::get('/patients', [ReceptionistController::class, 'patients'])->middleware('clinic.permission:patients.view');
    Route::get('/doctors', [ReceptionistController::class, 'doctors'])->middleware('clinic.permission:doctors.view');
    Route::get('/payments', [PaymentController::class, 'receptionistIndex'])->middleware('clinic.permission:payments.view');
    Route::patch('/payments/{payment}', [PaymentController::class, 'update'])->middleware('clinic.permission:payments.manage');
});
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::apiResource('users', AdminUserController::class);
    Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus']);
    Route::patch('/users/{id}/restore', [AdminUserController::class, 'restore']);
    Route::patch('/users/{user}/clinic-permissions', [AdminUserController::class, 'updateClinicPermissions']);
    Route::apiResource('clinics', AdminClinicController::class);
    Route::patch('/clinics/{id}/restore', [AdminClinicController::class, 'restore']);
    Route::apiResource('specializations', AdminSpecializationController::class);
    Route::apiResource('doctors', AdminDoctorController::class);
    Route::patch('/doctors/{doctor}/verify', [AdminDoctorController::class, 'updateVerification']);
    Route::patch('/doctors/{doctor}/clinic-permissions', [AdminDoctorController::class, 'updateClinicPermissions']);
    Route::apiResource('appointments', AdminAppointmentController::class)->only(['index', 'show', 'update']);
    Route::apiResource('payments', AdminPaymentController::class)->only(['index', 'show', 'update']);
    Route::get('/settings', [AdminSettingController::class, 'index']);
    Route::patch('/settings', [AdminSettingController::class, 'update']);
    Route::get('/reports', [AdminDashboardController::class, 'index']);
    Route::apiResource('reviews', AdminReviewController::class)->only(['index', 'destroy']);
    Route::patch('/reviews/{id}/restore', [AdminReviewController::class, 'restore']);
});
