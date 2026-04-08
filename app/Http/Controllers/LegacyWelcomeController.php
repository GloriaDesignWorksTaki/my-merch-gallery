<?php
/**
 * 旧 welcome 画面（開発用途）
 * @package App\Http\Controllers
 */
namespace App\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class LegacyWelcomeController extends Controller
{
  public function __invoke(): Response
  {
    return Inertia::render('Welcome', [
      'canLogin' => Route::has('login'),
      'canRegister' => Route::has('register'),
      'laravelVersion' => Application::VERSION,
      'phpVersion' => PHP_VERSION,
    ]);
  }
}
