<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\AdminAppointmentController;
use App\Http\Controllers\Api\Admin\AdminClinicController;
use App\Http\Controllers\Api\Admin\AdminDoctorController;
use App\Http\Controllers\Api\Admin\AdminSpecializationController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminPaymentController;
use App\Http\Controllers\Api\Admin\AdminReviewController;
use App\Http\Controllers\Admin\SystemSettingController;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('appointments', AdminAppointmentController::class)->only(['index', 'show', 'update']);
        Route::resource('payments', AdminPaymentController::class)->only(['index', 'show', 'update']);
        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SystemSettingController::class, 'update'])->name('settings.update');
        Route::resource('clinics', AdminClinicController::class);
        Route::resource('doctors', AdminDoctorController::class);
        Route::patch('doctors/{doctor}/verification', [AdminDoctorController::class, 'updateVerification'])->name('doctors.verification');
        Route::resource('specializations', AdminSpecializationController::class);
        Route::resource('users', AdminUserController::class);
    });
