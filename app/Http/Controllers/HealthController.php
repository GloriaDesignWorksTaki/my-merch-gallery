<?php
/**
 * ヘルスチェック用エンドポイント
 * @package App\Http\Controllers
 */
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
  public function __invoke(): JsonResponse
  {
    return response()->json(['status' => 'ok']);
  }
}
