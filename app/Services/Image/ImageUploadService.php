<?php
/**
 * マーチ画像のアップロード先パス生成
 * @package App\Services\Image
 */
namespace App\Services\Image;

use Illuminate\Http\UploadedFile;

class ImageUploadService
{
  public function storeMerchImage(UploadedFile $file): string
  {
    return $file->store('merch', ['disk' => 'uploads']);
  }
}
