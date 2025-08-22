<?php

namespace App\Observers;

use App\Models\WorldCupMatch;

class WorldCupMatchObserver
{
    public function updating(WorldCupMatch $model): void
    {
        $winnerId = $model->winner_id;

        if ($winnerId) {
            $model->is_live = false;
            $model->finished_at = now();

            $nextMatch = $model->nextMatch;

            if ($nextMatch) {
                !!$nextMatch->player_one_id ?
                    $nextMatch->update(['player_two_id' => $winnerId]) :
                    $nextMatch->update(['player_one_id' => $winnerId]);
            }
        }
    }
}
