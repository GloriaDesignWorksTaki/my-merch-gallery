<?php
/**
 * コレクション（一覧はプレースホルダー）
 * @package App\Http\Controllers\Collection
 */
namespace App\Http\Controllers\Collection;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController extends Controller
{
  public function index(): Response
  {
  return Inertia::render('Collections/Index');
  }
}
