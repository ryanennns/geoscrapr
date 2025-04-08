<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Player extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    public function teamAsA(): HasOne
    {
        return $this->hasOne(Team::class, 'player_a', 'user_id');
    }

    public function teamAsB(): HasOne
    {
        return $this->hasOne(Team::class, 'player_b', 'user_id');
    }

    public function getTeamAttribute(): ?Team
    {
        return $this->teamAsA()->first() ?? $this->teamAsB()->first();
    }
}
