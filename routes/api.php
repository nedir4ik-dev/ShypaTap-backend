<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\SpecializationController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AdminDashboardController;

use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminClinicController;
use App\Http\Controllers\Api\Admin\AdminSpecializationController;
use App\Http\Controllers\Api\Admin\AdminDoctorController;
use App\Http\Controllers\Api\Admin\AdminAppointmentController;

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{doctor}/slots', [DoctorController::class, 'slots']);
Route::get('/doctors/{doctor}', [DoctorController::class, 'show']);

Route::get('/specializations', [SpecializationController::class, 'index']);
Route::get('/clinics', [ClinicController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:patient')->group(function () {
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::get('/my-appointments', [AppointmentController::class, 'myAppointments']);
        Route::patch(
            '/appointments/{appointment}/cancel',
            [AppointmentController::class, 'cancel']
        );

        Route::post(
            '/appointments/{appointment}/review',
            [AppointmentController::class, 'storeReview']
        );
    });
    Route::middleware('role:doctor')
        ->prefix('doctor')
        ->group(function () {
            Route::get(
                '/appointments',
                [AppointmentController::class, 'doctorAppointments']
            );
            Route::patch(
                '/appointments/{appointment}/status',
                [AppointmentController::class, 'updateStatus']
            );
        });

    Route::middleware('role:receptionist,admin')
        ->prefix('receptionist')
        ->group(function () {

            Route::post(
                '/appointments',
                [AppointmentController::class, 'storeForPatient']
            );
        });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get(
            '/dashboard',
            [\App\Http\Controllers\Api\Admin\AdminDashboardController::class, 'index']
        );
        Route::apiResource('users', AdminUserController::class);
        Route::patch(
            '/users/{user}/status',
            [AdminUserController::class, 'updateStatus']
        );
        Route::apiResource('clinics', AdminClinicController::class);
        Route::apiResource(
            'specializations',
            AdminSpecializationController::class
        );
        Route::apiResource('doctors', AdminDoctorController::class);

        Route::patch(
            '/doctors/{doctor}/verify',
            [AdminDoctorController::class, 'verify']
        );
        Route::get(
            '/appointments',
            [AdminAppointmentController::class, 'index']
        );

        Route::get(
            '/appointments/{appointment}',
            [AdminAppointmentController::class, 'show']
        );
    });
});
