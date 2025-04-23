<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'team_id'  => $this->team_id,
            'name'     => $this->name,
            'rating'   => $this->rating,
            'player_a' => new PlayerResource($this->playerA),
            'player_b' => new PlayerResource($this->playerB),
        ];
    }
}
