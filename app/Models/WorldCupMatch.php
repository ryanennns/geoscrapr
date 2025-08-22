<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldCupMatch extends Model
{
    protected $guarded = [];

    protected $with = ['playerOne', 'playerTwo', 'winner'];

    public function playerOne(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_one_id', 'user_id');
    }

    public function playerTwo(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_two_id', 'user_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'winner_id', 'user_id');
    }
}
