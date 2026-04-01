<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:access-admin'])
  ->prefix('admin')
  ->name('admin.')
  ->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
  });
