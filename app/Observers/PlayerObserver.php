<?php

namespace App\Observers;

use App\Models\Player;
use App\Models\Team;

class PlayerObserver
{
    public function updating(Player $player): void
    {
        if (!$player->rating) {
            return;
        }

        if ($player->isDirty('rating')) {
            $player->ratingChanges()->create(['rating' => $player->rating]);
        }
    }

    public function created(Player $player): void
    {
        if (!$player->rating) {
            return;
        }

        $player->ratingChanges()->create(['rating' => $player->rating]);
    }
}
