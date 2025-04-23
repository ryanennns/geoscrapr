<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'user_id'      => $this->user_id,
            'name'         => $this->name,
            'rating'       => $this->rating,
            'country_code' => $this->country_code,
            'is_active'    => $this->created_at > Carbon::now()->subWeek(),
        ];
    }
}
