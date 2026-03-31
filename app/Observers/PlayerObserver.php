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

        if ($player->isDirty('moving_rating')) {
            $player->ratingChanges()->create(['rating' => $player->moving_rating, 'type' => 'moving']);
        }

        if ($player->isDirty('no_move_rating')) {
            $player->ratingChanges()->create(['rating' => $player->no_move_rating, 'type' => 'no_move']);
        }

        if ($player->isDirty('nmpz_rating')) {
            $player->ratingChanges()->create(['rating' => $player->nmpz_rating, 'type' => 'nmpz']);
        }
    }

    public function created(Player $player): void
    {
        if (!$player->rating) {
            return;
        }

        $player->ratingChanges()->create(['rating' => $player->rating]);

        if ($player->moving_rating) {
            $player->ratingChanges()->create(['rating' => $player->moving_rating, 'type' => 'moving']);
        }

        if ($player->no_move_rating) {
            $player->ratingChanges()->create(['rating' => $player->no_move_rating, 'type' => 'no_move']);
        }

        if ($player->nmpz_rating) {
            $player->ratingChanges()->create(['rating' => $player->nmpz_rating, 'type' => 'nmpz']);
        }
    }
}
