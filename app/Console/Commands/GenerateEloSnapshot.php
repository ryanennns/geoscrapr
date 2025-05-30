<?php

namespace App\Console\Commands;

use App\Models\EloSnapshot;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateEloSnapshot extends Command
{
    public const INTERVAL_SIZE = 100;

    protected $signature = 'snapshot:generate';
    protected $description = 'Generates an ELO snapshot based on playerbase stored in the db.';

    public function handle(): void
    {
        $maxElo = 3000;
        $bucketCount = $maxElo / self::INTERVAL_SIZE;

        $singleplayerBuckets = array_fill(0, $bucketCount, 0);
        try {
            $playerN = Player::query()
                ->whereNotNull('rating')
                ->count();

            Player::query()
                ->whereNotNull('rating')
                ->select('rating')
                ->chunk(100, function ($players) use (&$singleplayerBuckets) {
                    foreach ($players as $player) {
                        $bucketIndex = min((int)($player->rating / self::INTERVAL_SIZE), count($singleplayerBuckets) - 1);
                        $singleplayerBuckets[$bucketIndex]++;
                    }
                });

            EloSnapshot::query()->create([
                'date'     => now(),
                'gamemode' => 'solo',
                'buckets'  => collect($singleplayerBuckets)->mapWithKeys(function ($count, $i) {
                    $lower = $i * self::INTERVAL_SIZE;
                    $upper = $lower + self::INTERVAL_SIZE - 1;
                    return ["$lower-$upper" => $count];
                }),
                'n'        => $playerN,
            ]);

            Log::info("Generated singleplayer ELO snapshot for $playerN players");
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }

        $teamsBuckets = array_fill(0, $bucketCount, 0);
        try {
            $teamN = Team::query()
                ->whereNotNull('rating')
                ->count();

            Team::query()
                ->whereNotNull('rating')
                ->select('rating')->chunk(100, function ($teams) use (&$teamsBuckets) {
                    foreach ($teams as $team) {
                        $bucketIndex = min((int)($team->rating / self::INTERVAL_SIZE), count($teamsBuckets) - 1);
                        $teamsBuckets[$bucketIndex]++;
                    }
                });

            EloSnapshot::query()->create([
                'date'     => now(),
                'gamemode' => 'team',
                'buckets'  => collect($teamsBuckets)->mapWithKeys(function ($count, $i) {
                    $lower = $i * self::INTERVAL_SIZE;
                    $upper = $lower + self::INTERVAL_SIZE - 1;
                    return ["$lower-$upper" => $count];
                }),
                'n'        => $teamN,
            ]);

            Log::info("Generated teams ELO snapshot for $teamN players");
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }
    }
}
