<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\AdminClinicController;
use App\Http\Controllers\Api\Admin\AdminDoctorController;
use App\Http\Controllers\Api\Admin\AdminSpecializationController;
use App\Http\Controllers\Api\Admin\AdminUserController;

// Ähli admin routelary /admin prefixi we role:admin barlagy bilen
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Admin Dashboard sahypasy (Kontroller arkaly maglumatlary iberýär)
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Admin CRUD resurslary
        Route::resource('clinics', AdminClinicController::class);
        Route::resource('doctors', AdminDoctorController::class);
        Route::patch('doctors/{doctor}/verification', [AdminDoctorController::class, 'updateVerification'])->name('doctors.verification');
        Route::resource('specializations', AdminSpecializationController::class);
        Route::resource('users', AdminUserController::class);
    });
