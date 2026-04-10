<?php

namespace App\Http\Controllers;

use App\Services\SitemapBuilder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
  public function __invoke(SitemapBuilder $builder): Response
  {
    return response($builder->toXml(), 200, [
      'Content-Type' => 'application/xml; charset=UTF-8',
    ]);
  }
}
