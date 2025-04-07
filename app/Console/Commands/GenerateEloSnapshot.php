<?php

namespace App\Console\Commands;

use App\Models\EloSnapshot;
use App\Models\Player;
use Illuminate\Console\Command;

class GenerateEloSnapshot extends Command
{
    public const INTERVAL_SIZE = 100;

    protected $signature = 'snapshot:generate';
    protected $description = 'Generates an ELO snapshot based on playerbase stored in the db.';

    public function handle(): void
    {
        $maxElo = 3000;
        $bucketCount = $maxElo / self::INTERVAL_SIZE;
        $buckets = array_fill(0, $bucketCount, 0);

        try {
            Player::query()
                ->select('rating')
                ->chunk(100, function ($players) use (&$buckets) {
                    foreach ($players as $player) {
                        $bucketIndex = min((int)($player->rating / self::INTERVAL_SIZE), count($buckets) - 1);
                        $buckets[$bucketIndex]++;
                    }
                });

            EloSnapshot::query()->create([
                'date' => now(),
                'buckets' => collect($buckets)->mapWithKeys(function ($count, $i) {
                    $lower = $i * self::INTERVAL_SIZE;
                    $upper = $lower + self::INTERVAL_SIZE - 1;
                    return ["{$lower}-{$upper}" => $count];
                }),
            ]);
        } catch (\Throwable $exception) {
            dd($exception->getMessage());
        }
    }
}
