<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'name'           => $this->name,
            'rating'         => $this->rating,
            'moving_rating'  => $this->moving_rating,
            'no_move_rating' => $this->no_move_rating,
            'nmpz_rating'    => $this->nmpz_rating,
            'country_code'   => $this->country_code,
        ];
    }
}
