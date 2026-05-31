<?php

namespace App\Console\Commands;

use App\Models\Player;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class NullPlayerGamesPlayed extends Command
{
    protected $signature = 'players:null-games-played';

    protected $description = 'Sets all games played columns on players to null.';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        DB::transaction(function () {
            Player::query()->update([
                'ranked_duels_played' => null,
                'single_player_games_played' => null,
                'unranked_duels_played' => null,
                'ranked_team_duels_played' => null,
                'unranked_team_duels_played' => null,
            ]);
        });

        return self::SUCCESS;
    }
}
