<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

abstract class Controller
{
  use AuthorizesRequests;

  /**
   * @return array<int, int>
   */
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

  /**
   * @return array<string, mixed>
   */
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

  /**
   * @return array<string, mixed>
   */
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
