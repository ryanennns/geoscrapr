<?php

namespace App\Observers;

use App\Models\RatingChange;
use App\Models\Team;
use Illuminate\Support\Facades\Log;

class TeamObserver
{
    public function updating(Team $team): void
    {
        try {
            if ($team->isDirty('rating')) {
                $team->ratingChanges()->create(['rating' => $team->rating]);
            }
        } catch (\Exception $e) {
            Log::info('Failed to update team rating: ' . $e->getMessage(), ['team' => $team->toArray()]);
        }
    }

    public function created(Team $team): void
    {
        try {
            $team->ratingChanges()->create(['rating' => $team->rating]);
        } catch (\Exception $e) {
            Log::info('Failed to update team rating: ' . $e->getMessage(), ['team' => $team->toArray()]);
        }
    }
}
