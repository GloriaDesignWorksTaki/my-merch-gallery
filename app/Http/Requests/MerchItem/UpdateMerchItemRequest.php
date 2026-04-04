<?php

namespace App\Http\Requests\MerchItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateMerchItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'band_id' => ['required', 'integer', 'exists:bands,id'],
            'merch_category_id' => ['required', 'integer', 'exists:merch_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'release_year' => ['nullable', 'integer', 'between:1900,2100'],
            'size_note' => ['nullable', 'string', 'max:255'],
            'is_official' => ['required', 'boolean'],
            'source_type' => ['required', 'string', 'max:50'],
            'existing_image_ids' => ['nullable', 'array'],
            'existing_image_ids.*' => ['integer', 'exists:merch_images,id'],
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $existingImageCount = count($this->input('existing_image_ids', []));
            $newImageCount = count($this->file('images', []));

            if (($existingImageCount + $newImageCount) > 4) {
                $validator->errors()->add('images', '画像は合計4枚まで登録できます。');
            }
        });
    }
}
