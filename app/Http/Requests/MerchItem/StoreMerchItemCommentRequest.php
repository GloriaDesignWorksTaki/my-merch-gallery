<?php

namespace App\Http\Requests\MerchItem;

use App\Models\MerchItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class StoreMerchItemCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string|Exists>>
     */
    public function rules(): array
    {
        /** @var MerchItem $merch */
        $merch = $this->route('merchItem');

        return [
            'body' => ['required', 'string', 'max:5000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('merch_item_comments', 'id')->where('merch_item_id', $merch->id),
            ],
        ];
    }
}
