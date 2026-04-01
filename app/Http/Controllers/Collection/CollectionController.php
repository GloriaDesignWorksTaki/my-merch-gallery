<?php

namespace App\Http\Controllers\Collection;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController extends Controller
{
  /**
     * collections テーブル導入まではプレースホルダー。
     */
  public function index(): Response
  {
    return Inertia::render('Collections/Index');
  }
}
