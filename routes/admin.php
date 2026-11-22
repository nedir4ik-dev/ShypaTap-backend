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
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\PermissionController;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::patch('users/{user}/clinic-permissions', [AdminUserController::class, 'updateClinicPermissions'])->name('users.clinic-permissions');
        Route::patch('doctors/{doctor}/clinic-permissions', [AdminDoctorController::class, 'updateClinicPermissions'])->name('doctors.clinic-permissions');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

        Route::resource('appointments', AdminAppointmentController::class)->only(['index', 'show', 'update']);
        Route::resource('payments', AdminPaymentController::class)->only(['index', 'show', 'update']);
        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::patch('reviews/{id}/restore', [AdminReviewController::class, 'restore'])->name('reviews.restore');
        Route::get('settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SystemSettingController::class, 'update'])->name('settings.update');
        Route::resource('clinics', AdminClinicController::class);
        Route::patch('clinics/{id}/restore', [AdminClinicController::class, 'restore'])->name('clinics.restore');
        Route::resource('doctors', AdminDoctorController::class);
        Route::patch('doctors/{doctor}/verification', [AdminDoctorController::class, 'updateVerification'])->name('doctors.verification');
        Route::resource('specializations', AdminSpecializationController::class);
        Route::resource('users', AdminUserController::class);
        Route::patch('users/{id}/restore', [AdminUserController::class, 'restore'])->name('users.restore');
    });
