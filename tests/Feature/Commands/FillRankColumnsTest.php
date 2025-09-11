<?php

namespace Tests\Feature\Commands;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FillRankColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_all_country_codes_relating_to_players(): void
    {
        $this->markTestSkipped('new version of command does not co-operate with sqlite in ci');

        $topRatedPlayer = Player::factory()->create(['rating' => 2000]);
        $midRatedPlayer = Player::factory()->create(['rating' => 1500]);
        $lowRatedPlayer = Player::factory()->create(['rating' => 1000]);

        collect([
            $topRatedPlayer,
            $midRatedPlayer,
            $lowRatedPlayer,
        ])->each(function (Player $player) {
            $this->assertNull($player->rank);
        });

        $this->artisan('rankings:generate');

        collect([
            $topRatedPlayer,
            $midRatedPlayer,
            $lowRatedPlayer,
        ])->each(function (Player $player) {
            $this->assertNotNull($player->refresh()->rank);
            $this->assertNotNull($player->refresh()->percentile);
        });

        $this->assertEquals(1, $topRatedPlayer->refresh()->rank);
        $this->assertEquals(100, $topRatedPlayer->refresh()->percentile);
        $this->assertEquals(2, $midRatedPlayer->refresh()->rank);
        $this->assertEquals(50, $midRatedPlayer->refresh()->percentile);
        $this->assertEquals(3, $lowRatedPlayer->refresh()->rank);
        $this->assertEquals(0, $lowRatedPlayer->refresh()->percentile);

        $this->assertTrue(true);
    }
}
