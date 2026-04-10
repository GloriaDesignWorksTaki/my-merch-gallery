<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsTxtController extends Controller
{
  public function __invoke(): Response
  {
    $body = implode("\n", [
      'User-agent: *',
      'Disallow:',
      '',
      'Sitemap: '.route('sitemap', [], true),
    ])."\n";

    return response($body, 200, [
      'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
  }
}
