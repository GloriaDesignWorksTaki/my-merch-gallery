<?php
/**
 * バンド新規作成のバリデーション
 * @package App\Http\Requests\Band
 */
namespace App\Http\Requests\Band;

use App\Http\Requests\Band\Concerns\ValidatesBandPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBandRequest extends FormRequest
{
  use ValidatesBandPayload;

  public function authorize(): bool
  {
    $user = $this->user();

    return $user !== null && ! $user->isBanned();
  }

  public function rules(): array
  {
    $rules = $this->bandPayloadRules();
    unset($rules['remove_image']);

    return $rules;
  }

  public function withValidator(Validator $validator): void
  {
    $this->withBandPayloadValidator($validator, null);
  }
}
