<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BandEditRequestAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('/band-edit-requests', [BandEditRequestAdminController::class, 'index'])->name('band-edit-requests.index');
        Route::post('/band-edit-requests/{bandEditRequest}/approve', [BandEditRequestAdminController::class, 'approve'])->name('band-edit-requests.approve');
        Route::post('/band-edit-requests/{bandEditRequest}/reject', [BandEditRequestAdminController::class, 'reject'])->name('band-edit-requests.reject');

        Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/ban', [UserAdminController::class, 'ban'])->name('users.ban');
        Route::post('/users/{user}/unban', [UserAdminController::class, 'unban'])->name('users.unban');
        Route::patch('/users/{user}/role', [UserAdminController::class, 'updateRole'])->name('users.role');
    });
