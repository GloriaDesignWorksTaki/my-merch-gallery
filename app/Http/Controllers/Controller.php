<?php
/**
 * コントローラ共通ヘルパ（クエリ組み立てなど）
 * @package App\Http\Controllers
 */
namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

abstract class Controller
{
  use AuthorizesRequests;

  // リクエストからバンド ID 配列（フィルタ用）
  protected function bandIdsFromRequest(Request $request): array
  {
    $raw = $request->input('bands');
    if (is_array($raw)) {
      $ids = array_values(array_unique(array_filter(array_map('intval', $raw), fn ($id) => $id > 0)));
      sort($ids);

      return $ids;
    }

    $legacy = $request->integer('band');

    return $legacy > 0 ? [$legacy] : [];
  }

  // マーチ一覧に戻すときのクエリ文字列用
  protected function merchIndexQueryForReturn(Request $request): array
  {
    $bands = $this->bandIdsFromRequest($request);

    $params = array_filter([
      'page' => $request->query('page'),
      'search' => $request->query('search'),
      'category' => $request->query('category'),
      'sort' => $request->query('sort'),
    ], fn ($value) => filled($value));

    if (count($bands) > 0) {
      $params['bands'] = $bands;
    }

    return $params;
  }

  // 投稿一覧に戻すときのクエリ文字列用
  protected function postsIndexQueryForReturn(Request $request): array
  {
    $bands = $this->bandIdsFromRequest($request);

    $params = array_filter([
      'page' => $request->query('page'),
      'search' => $request->query('search'),
      'visibility' => $request->query('visibility'),
      'sort' => $request->query('sort'),
    ], fn ($value) => filled($value));

    if (count($bands) > 0) {
      $params['bands'] = $bands;
    }

    return $params;
  }
}
