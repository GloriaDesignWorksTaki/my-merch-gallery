<?php
/**
 * ヘッダー通知ドロップダウン用 JSON
 * @package App\Http\Controllers
 */
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationDropdownController extends Controller
{
  public function show(Request $request): JsonResponse
  {
    $rows = $request->user()
      ->notifications()
      ->latest()
      ->limit(8)
      ->get()
      ->map(fn ($n) => [
        'id' => $n->id,
        'read' => $n->read_at !== null,
        'created_at' => $n->created_at?->toIso8601String(),
        'data' => $n->data,
      ])
      ->values()
      ->all();

    return response()->json(['recent' => $rows]);
  }
}
