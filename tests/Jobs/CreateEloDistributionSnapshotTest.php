<?php

namespace Tests\Jobs;

use App\Jobs\CreateEloDistributionSnapshot;
use App\Models\EloSnapshot;
use App\Models\Player;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateEloDistributionSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_only_includes_rateables_that_played_since_the_rating_correction(): void
    {
        $playerA = Player::factory()->create(['user_id' => 'player-a', 'rating' => null]);
        $playerB = Player::factory()->create(['user_id' => 'player-b', 'rating' => null]);

        Carbon::setTestNow('2026-06-28 12:00:00');
        Player::factory()->create(['rating' => 2500]);
        Team::factory()->create([
            'rating'   => 2500,
            'player_a' => $playerA->user_id,
            'player_b' => $playerB->user_id,
        ]);

        Carbon::setTestNow('2026-06-29 00:00:00');
        Player::factory()->create(['rating' => 2000]);
        Team::factory()->create([
            'rating'   => 2000,
            'player_a' => $playerA->user_id,
            'player_b' => $playerB->user_id,
        ]);

        Carbon::setTestNow('2026-06-30 12:00:00');

        (new CreateEloDistributionSnapshot)->handle();

        $soloSnapshot = EloSnapshot::query()
            ->where('gamemode', 'solo')
            ->where('type', EloSnapshot::TYPE_ELO_RANGE)
            ->firstOrFail();
        $teamSnapshot = EloSnapshot::query()
            ->where('gamemode', 'team')
            ->where('type', EloSnapshot::TYPE_ELO_RANGE)
            ->firstOrFail();

        $this->assertSame(1, $soloSnapshot->n);
        $this->assertSame(1, json_decode($soloSnapshot->buckets, true)['2000-2099']);
        $this->assertSame(0, json_decode($soloSnapshot->buckets, true)['2500-2599']);
        $this->assertSame(1, $teamSnapshot->n);
        $this->assertSame(1, json_decode($teamSnapshot->buckets, true)['2000-2099']);
        $this->assertSame(0, json_decode($teamSnapshot->buckets, true)['2500-2599']);
    }
}
