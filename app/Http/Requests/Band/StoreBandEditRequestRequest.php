<?php

namespace App\Http\Requests\Band;

use App\Http\Requests\Band\Concerns\ValidatesBandPayload;
use App\Models\Band;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBandEditRequestRequest extends FormRequest
{
    use ValidatesBandPayload;

    public function authorize(): bool
    {
        $band = $this->route('band');

        return $band instanceof Band
            && $this->user() !== null
            && $this->user()->can('createEditRequest', $band);
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
