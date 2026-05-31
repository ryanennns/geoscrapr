<?php

namespace Tests\Feature\Commands;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NullPlayerGamesPlayedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sets_all_games_played_columns_to_null(): void
    {
        $player = Player::factory()->create([
            'ranked_duels_played' => 1,
            'single_player_games_played' => 2,
            'unranked_duels_played' => 3,
            'ranked_team_duels_played' => 4,
            'unranked_team_duels_played' => 5,
        ]);

        $this->artisan('players:null-games-played')
            ->assertExitCode(0);

        $player->refresh();

        $this->assertNull($player->ranked_duels_played);
        $this->assertNull($player->single_player_games_played);
        $this->assertNull($player->unranked_duels_played);
        $this->assertNull($player->ranked_team_duels_played);
        $this->assertNull($player->unranked_team_duels_played);
    }
}
