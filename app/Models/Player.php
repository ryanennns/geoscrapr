<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Player extends Model
{
    use HasFactory;
    use HasUuids;

    public const BLACKLIST = [
        '5759d0d2c3eb4f349c4974fa',
    ];

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

    public function ratingChanges(): MorphMany
    {
        return $this->morphMany(RatingChange::class, 'rateable');
    }

    public function scopeIsActive(Builder $query): void
    {
        $query->where('updated_at', '>=', Carbon::now()->subWeek());
    }

    public function scopePlayedSinceRatingCorrection(Builder $query): void
    {
        $query->whereHas('ratingChanges', function (Builder $query) {
            $query->where('created_at', '>=', Carbon::parse(RatingChange::RATING_CORRECTION_CUTOFF));
        });
    }
}
