<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;

class ImageUploadService
{
    /**
     * @return non-falsy-string ストレージ上のパス
     */
    public function storeMerchImage(UploadedFile $file): string
    {
        return $file->store('merch', ['disk' => 'public']);
    }
}
