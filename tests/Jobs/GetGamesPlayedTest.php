<?php

namespace Tests\Jobs;

use App\Jobs\GetGamesPlayed;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;
use Tests\TestCase;

class GetGamesPlayedTest extends TestCase
{
    use RefreshDatabase;

    #[NoReturn]
    public function test_it_updates_rating_and_creates_rating_change()
    {
        $contents = file_get_contents('tests/Jobs/Fixtures/GamesPlayed.html');

        $player = Player::factory()->create([
            'user_id' => Str::uuid()->toString(),
            'nmpz_rating' => 1000,
            'moving_rating' => 1000,
            'ranked_duels_played' => 0,
        ]);

        Http::fake([
            '*' => Http::response($contents),
        ]);

        new GetGamesPlayed(0)->handle();

        $this->assertDatabaseHas('players', [
            'user_id' => $player->user_id,
            'ranked_duels_played' => 219,
        ]);
    }
}
