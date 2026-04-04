<?php
/**
 * バンド共通フィールドのルール
 * @package App\Http\Requests\Band\Concerns
 */
namespace App\Http\Requests\Band\Concerns;

use App\Models\Band;
use Illuminate\Validation\Validator;

trait ValidatesBandPayload
{
  public function bandPayloadRules(): array
  {
    return [
      'name' => ['required', 'string', 'max:255'],
      'country_id' => ['nullable', 'integer', 'exists:countries,id'],
      'genre_ids' => ['nullable', 'array', 'max:10'],
      'genre_ids.*' => ['integer', 'exists:genres,id'],
      'links' => ['nullable', 'array', 'max:3'],
      'links.*' => ['nullable', 'url', 'max:2048'],
      'description' => ['nullable', 'string', 'max:5000'],
      'formed_year' => ['nullable', 'integer', 'between:1900,2100'],
      'is_active' => ['required', 'boolean'],
      'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
      'remove_image' => ['sometimes', 'boolean'],
    ];
  }

  public function withBandPayloadValidator(Validator $validator, ?Band $routeBand): void
  {
    $validator->after(function (Validator $validator) use ($routeBand) {
      $band = $routeBand;
      $normalizedName = Band::normalizeComparableName((string) $this->input('name'));

      if ($normalizedName === '') {
        return;
      }

      $duplicateExists = Band::query()
        ->select(['id', 'name'])
        ->when($band !== null, fn ($query) => $query->whereKeyNot($band->id))
        ->get()
        ->contains(fn (Band $existingBand) => Band::normalizeComparableName($existingBand->name) === $normalizedName);

      if ($duplicateExists) {
        $validator->errors()->add('name', '同じバンド名はすでに登録されています。');
      }
    });
  }
}
