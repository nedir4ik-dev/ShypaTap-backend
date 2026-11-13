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
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'read']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);
});
Route::middleware(['auth:sanctum', 'role:patient'])->group(function () {
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']);
    Route::patch('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/appointments/{id}/review', [AppointmentController::class, 'storeReview']);
});
Route::middleware(['auth:sanctum', 'role:doctor'])->prefix('doctor')->group(function () {
    Route::get('/dashboard', [AppointmentController::class, 'doctorDashboard']);
    Route::get('/appointments', [AppointmentController::class, 'doctorAppointments']);
    Route::get('/schedules', [DoctorScheduleController::class, 'index']);
    Route::post('/schedules', [DoctorScheduleController::class, 'store']);
    Route::put('/schedules/{id}', [DoctorScheduleController::class, 'update']);
    Route::delete('/schedules/{id}', [DoctorScheduleController::class, 'destroy']);
    Route::get('/days-off', [DoctorScheduleController::class, 'daysOff']);
    Route::post('/days-off', [DoctorScheduleController::class, 'addDayOff']);
    Route::delete('/days-off/{id}', [DoctorScheduleController::class, 'deleteDayOff']);
    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);
});
Route::middleware(['auth:sanctum', 'role:receptionist,admin'])->prefix('receptionist')->group(function () {
    Route::get('/dashboard', [ReceptionistController::class, 'dashboard']);
    Route::get('/appointments', [ReceptionistController::class, 'appointments']);
    Route::post('/appointments', [AppointmentController::class, 'storeForPatient']);
    Route::get('/patients', [ReceptionistController::class, 'patients']);
    Route::get('/doctors', [ReceptionistController::class, 'doctors']);
});
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::apiResource('users', AdminUserController::class);
    Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus']);
    Route::apiResource('clinics', AdminClinicController::class);
    Route::apiResource('specializations', AdminSpecializationController::class);
    Route::apiResource('doctors', AdminDoctorController::class);
    Route::patch('/doctors/{doctor}/verify', [AdminDoctorController::class, 'updateVerification']);
    Route::apiResource('appointments', AdminAppointmentController::class)->only(['index', 'show', 'update']);
    Route::apiResource('payments', AdminPaymentController::class)->only(['index', 'show', 'update']);
    Route::get('/settings', [AdminSettingController::class, 'index']);
    Route::patch('/settings', [AdminSettingController::class, 'update']);
    Route::get('/reports', [AdminDashboardController::class, 'index']);
});
