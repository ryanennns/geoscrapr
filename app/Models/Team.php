<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;

class Team extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    public function playerA(): HasOne
    {
        return $this->hasOne(Player::class, 'user_id', 'player_a');
    }

    public function playerB(): HasOne
    {
        return $this->hasOne(Player::class, 'user_id', 'player_b');
    }

    public function ratingChanges(): MorphMany
    {
        return $this->morphMany(RatingChange::class, 'rateable');
    }

    public function scopeIsActive(Builder $query): void
    {
        $query->where('updated_at', '>=', Carbon::now()->subWeek());
    }
}
