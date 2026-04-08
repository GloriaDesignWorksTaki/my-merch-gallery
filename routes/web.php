<?php

use App\Http\Controllers\Band\BandController;
use App\Http\Controllers\Band\BandEditRequestController;
use App\Http\Controllers\Band\BandLikeController;
use App\Http\Controllers\Collection\CollectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardLikesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MerchItem\MerchItemCommentController;
use App\Http\Controllers\MerchItem\MerchItemCommentLikeController;
use App\Http\Controllers\MerchItem\MerchItemController;
use App\Http\Controllers\MerchItem\MerchItemLikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationDropdownController;
use App\Http\Controllers\Profile\PublicProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');

Route::post('/locale', LocaleController::class)->name('locale.update');

Route::get('/bands', [BandController::class, 'index'])->name('bands.index');

Route::get('/merch-items', [MerchItemController::class, 'index'])->name('merch-items.index');

Route::get('/search', SearchController::class)->name('search');

Route::get('/users/{user}', [PublicProfileController::class, 'show'])->whereNumber('user')->name('users.show');

Route::get('/welcome-legacy', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome.legacy');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard/likes', DashboardLikesController::class)->middleware(['auth', 'verified'])->name('dashboard.likes');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/dropdown', [NotificationDropdownController::class, 'show'])->name('notifications.dropdown');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/api/bands/{band:id}/merch-items/options', [MerchItemController::class, 'options'])->whereNumber('band')->name('bands.merch-items.options');

    Route::get('/bands/create', [BandController::class, 'create'])->name('bands.create');
    Route::post('/bands', [BandController::class, 'store'])->name('bands.store');
    Route::get('/bands/{band:slug}/edit', [BandController::class, 'edit'])->name('bands.edit');
    Route::patch('/bands/{band:slug}', [BandController::class, 'update'])->name('bands.update');
    Route::get('/bands/{band:slug}/edit-request', [BandEditRequestController::class, 'create'])->name('bands.edit-request.create');
    Route::post('/bands/{band:slug}/edit-request', [BandEditRequestController::class, 'store'])->name('bands.edit-request.store');

    Route::get('/merch-items/create', [MerchItemController::class, 'create'])->name('merch-items.create');
    Route::post('/merch-items', [MerchItemController::class, 'store'])->name('merch-items.store');
    Route::get('/merch-items/{merchItem:slug}/edit', [MerchItemController::class, 'edit'])->name('merch-items.edit');
    Route::patch('/merch-items/{merchItem:slug}', [MerchItemController::class, 'update'])->name('merch-items.update');

    Route::post('/merch-items/{merchItem:slug}/comments', [MerchItemCommentController::class, 'store'])->name('merch-items.comments.store');
    Route::delete('/merch-items/{merchItem:slug}/comments/{merchItemComment}', [MerchItemCommentController::class, 'destroy'])->name('merch-items.comments.destroy');
    Route::post('/merch-items/{merchItem:slug}/comments/{merchItemComment}/like', [MerchItemCommentLikeController::class, 'toggle'])->name('merch-items.comments.like.toggle');

    Route::post('/merch-items/{merchItem:slug}/like', [MerchItemLikeController::class, 'toggle'])->name('merch-items.like.toggle');

    Route::post('/bands/{band:slug}/like', [BandLikeController::class, 'toggle'])->name('bands.like.toggle');

    Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
});

Route::get('/bands/{band:slug}', [BandController::class, 'show'])->name('bands.show');

Route::get('/merch-items/{merchItem:slug}', [MerchItemController::class, 'show'])->name('merch-items.show');

require __DIR__.'/auth.php';
