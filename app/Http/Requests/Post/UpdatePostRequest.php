<?php

namespace App\Http\Requests\Post;

use App\Models\MerchItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
  public function authorize(): bool
  {
    return $this->user() !== null;
  }

  public function rules(): array
  {
    return [
      'band_id' => ['required', 'integer', 'exists:bands,id'],
      'merch_item_id' => [
        'nullable',
        'integer',
        'exists:merch_items,id',
        function (string $attribute, mixed $value, \Closure $fail): void {
          if ($value === null || $value === '') {
            return;
          }

          $merchItem = MerchItem::query()->find($value);

          if ($merchItem && (int) $merchItem->band_id !== (int) $this->input('band_id')) {
            $fail('選択したマーチはバンドと一致していません。');
          }
        },
      ],
      'body' => ['required', 'string', 'max:5000'],
      'visibility' => ['required', Rule::in(['public', 'private', 'unlisted'])],
      'images' => ['nullable', 'array', 'max:4'],
      'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
    ];
  }
}
