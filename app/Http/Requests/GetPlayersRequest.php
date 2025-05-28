<?php

namespace App\Http\Requests;

use App\Http\Enums\CountryCode;
use App\Http\Enums\SortOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetPlayersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country' => Rule::enum(CountryCode::class),
            'order'   => Rule::enum(SortOrder::class),
            'active'  => 'boolean|nullable',
            'game_type' => [
                'string',
                'nullable',
                Rule::in(['moving', 'no_move', 'nmpz']),
            ],
        ];
    }
}
