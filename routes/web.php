<?php

use App\Http\Controllers\Band\BandController;
use App\Http\Controllers\Collection\CollectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MerchItem\MerchItemController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\Profile\PublicProfileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');

Route::get('/bands', [BandController::class, 'index'])->name('bands.index');
Route::get('/merch-items', [MerchItemController::class, 'index'])->name('merch-items.index');

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->whereNumber('post')->name('posts.show');

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

Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
  Route::get('/api/bands/{band:id}/merch-items/options', [MerchItemController::class, 'options'])->whereNumber('band')->name('bands.merch-items.options');

  Route::get('/bands/create', [BandController::class, 'create'])->name('bands.create');
  Route::post('/bands', [BandController::class, 'store'])->name('bands.store');
  Route::get('/bands/{band:slug}/edit', [BandController::class, 'edit'])->name('bands.edit');
  Route::patch('/bands/{band:slug}', [BandController::class, 'update'])->name('bands.update');

  Route::get('/merch-items/create', [MerchItemController::class, 'create'])->name('merch-items.create');
  Route::post('/merch-items', [MerchItemController::class, 'store'])->name('merch-items.store');
  Route::get('/merch-items/{merchItem:slug}/edit', [MerchItemController::class, 'edit'])->name('merch-items.edit');
  Route::patch('/merch-items/{merchItem:slug}', [MerchItemController::class, 'update'])->name('merch-items.update');

  Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
  Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
  Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->whereNumber('post')->name('posts.edit');
  Route::patch('/posts/{post}', [PostController::class, 'update'])->whereNumber('post')->name('posts.update');

  Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
});

Route::get('/bands/{band:slug}', [BandController::class, 'show'])->name('bands.show');
Route::get('/merch-items/{merchItem:slug}', [MerchItemController::class, 'show'])->name('merch-items.show');

require __DIR__.'/auth.php';
