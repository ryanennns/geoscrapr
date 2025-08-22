<?php

namespace App\Observers;

use App\Events\MatchUpdated;
use App\Models\WorldCupMatch;

class WorldCupMatchObserver
{
    public function updating(WorldCupMatch $match): void
    {
        $winnerId = $match->winner_id;

        if ($winnerId) {
            $match->is_live = false;
            $match->finished_at = now();

            $nextMatch = $match->nextMatch;

            if ($nextMatch) {
                !!$nextMatch->player_one_id ?
                    $nextMatch->update(['player_two_id' => $winnerId]) :
                    $nextMatch->update(['player_one_id' => $winnerId]);
            }
        }
    }

    public function updated(WorldCupMatch $match): void
    {
        MatchUpdated::dispatch($match);
    }
}
