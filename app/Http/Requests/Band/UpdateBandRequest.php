<?php

namespace App\Http\Requests\Band;

use App\Http\Requests\Band\Concerns\ValidatesBandPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBandRequest extends FormRequest
{
    use ValidatesBandPayload;

    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('update', $this->route('band'));
    }

    public function rules(): array
    {
        return $this->bandPayloadRules();
    }

    public function withValidator(Validator $validator): void
    {
        $this->withBandPayloadValidator($validator, $this->route('band'));
    }
}
