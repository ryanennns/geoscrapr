<?php

namespace Feature\Controllers;

use App\Models\EloSnapshot;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetSnapshotForDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_snapshot_for_date()
    {
        $date = Carbon::now();

        $solo = EloSnapshot::factory()->create(['date' => $date, 'gamemode' => 'solo']);
        $team = EloSnapshot::factory()->create(['date' => $date, 'gamemode' => 'team']);

        $response = $this->getJson('snapshots?date=' . $date->format('Y-m-d'));

        $response->assertOk();
        $response->assertJson([
            'solo' => [
                'date'    => $date->format('Y-m-d'),
                'buckets' => json_decode($solo->buckets, true),
                'n'       => $solo->n,
            ],
            'team' => [
                'date'    => $date->format('Y-m-d'),
                'buckets' => json_decode($team->buckets, true),
                'n'       => $team->n,
            ]
        ]);
    }

    public function test_it_returns_unprocessable_if_no_date()
    {
        $this->getJson('snapshots')->assertUnprocessable();
    }

    public function test_it_returns_unprocessable_if_invalid_date()
    {
        $this->getJson('snapshots?date=' . 'not-a-date')->assertUnprocessable();
    }
}
