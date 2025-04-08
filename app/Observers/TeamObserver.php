<?php

namespace App\Observers;

use App\Models\RatingChange;
use App\Models\Team;

class TeamObserver
{
    public function updating(Team $team): void
    {
        if ($team->isDirty('rating')) {
            $team->ratingChanges()->create(['rating' => $team->rating]);
        }
    }

    public function created(Team $team): void
    {
        $team->ratingChanges()->create(['rating' => $team->rating]);
    }
}
