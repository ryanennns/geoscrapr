<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    public function playerA()
    {
        return $this->hasOne(Player::class, 'user_id', 'player_a');
    }

    public function playerB()
    {
        return $this->hasOne(Player::class, 'user_id', 'player_b');
    }

    public function ratingChanges()
    {
        return $this->morphMany(RatingChange::class, 'rateable');
    }
}
