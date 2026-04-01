<?php

namespace App\Http\Requests\Band;

use App\Models\Band;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBandRequest extends FormRequest
{
  public function authorize(): bool
  {
    return $this->user() !== null;
  }

  public function rules(): array
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
    ];
  }

  public function withValidator(Validator $validator): void
  {
    $validator->after(function (Validator $validator) {
      $band = $this->route('band');
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
