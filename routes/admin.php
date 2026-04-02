<?php
/**
 * 管理者向けroot
 * @description 管理者向け管理画面を渡す
 * @author Gloria Design Works
 * @copyright 2026 Gloria Design Works
 * @version 1.00.000
*/
use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:access-admin'])
  ->prefix('admin')
  ->name('admin.')
  ->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
  });
