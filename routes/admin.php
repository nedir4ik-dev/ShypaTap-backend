<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Api\Admin\AdminAppointmentController;
use App\Http\Controllers\Api\Admin\AdminClinicController;
use App\Http\Controllers\Api\Admin\AdminDoctorController;
use App\Http\Controllers\Api\Admin\AdminSpecializationController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminPaymentController;
use App\Http\Controllers\Api\Admin\AdminReviewController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Api\Admin\TrashController;
use App\Http\Controllers\Admin\RolePageController;
use App\Http\Controllers\Admin\ReportPageController;

Route::middleware(['auth', 'role:admin', 'permission:auto'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/profile', [ProfileController::class, 'edit'])->middleware('permission:profile.view')->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->middleware('permission:profile.update')->name('profile.update');
        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->middleware('permission:profile.update')->name('profile.avatar.upload');
        Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->middleware('permission:profile.update')->name('profile.avatar.delete');

        Route::get('/reports', [ReportPageController::class, 'index'])->middleware('permission:reports.view')->name('reports.index');
        Route::get('/reports/export', [ReportPageController::class, 'export'])->middleware('permission:reports.export')->name('reports.export');

        Route::get('/roles', [RolePageController::class, 'index'])->middleware('permission:roles.manage')->name('roles.index');
        Route::post('/roles', [RolePageController::class, 'store'])->middleware('permission:roles.manage')->name('roles.store');
        Route::put('/roles/{role}', [RolePageController::class, 'update'])->middleware('permission:roles.manage')->name('roles.update');
        Route::post('/permissions', [RolePageController::class, 'storePermission'])->middleware('permission:permissions.assign')->name('permissions.store');
        Route::put('/users/{user}/overrides', [RolePageController::class, 'assignOverrides'])->middleware('permission:permissions.assign')->name('users.overrides');
        Route::put('/users/{user}/roles', [RolePageController::class, 'assignUser'])->middleware('permission:roles.manage')->name('users.roles');
        Route::delete('/roles/{role}', [RolePageController::class, 'destroy'])->middleware('permission:roles.manage')->name('roles.destroy');

        Route::get('/trash', [TrashController::class, 'index'])->middleware('permission:trash.view')->name('trash.index');
        Route::post('/trash/{type}/{id}/restore', [TrashController::class, 'restore'])->middleware('permission:trash.restore')->name('trash.restore');
        Route::delete('/trash/{type}/{id}', [TrashController::class, 'forceDelete'])->middleware('permission:trash.force_delete')->name('trash.force-delete');

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('appointments', AdminAppointmentController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::resource('payments', AdminPaymentController::class)->only(['index', 'show', 'update']);
        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SystemSettingController::class, 'update'])->name('settings.update');
        Route::resource('clinics', AdminClinicController::class);
        Route::resource('doctors', AdminDoctorController::class);
        Route::patch('doctors/{doctor}/verification', [AdminDoctorController::class, 'updateVerification'])->name('doctors.verification');
        Route::resource('specializations', AdminSpecializationController::class);
        Route::get('patients', [AdminUserController::class, 'patients'])->middleware('permission:patients.view')->name('patients.index');
        Route::resource('users', AdminUserController::class);
    });
