<?php

namespace App\Console\Commands;

use App\Models\EloSnapshot;
use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GeneratePercentileSnapshot extends Command
{
    protected $signature = 'snapshot:percentile';

    protected $description = 'Command description';

    private function determinePercentile($i, $k, &$ratingMap): void
    {
        $multiple = $i / 100;
        $index = round($k * $multiple);

        $percentileUser = Player::query()
            ->whereNotNull('rating')
            ->orderBy('rating')
            ->select('rating')
            ->pluck('rating')
            ->skip($index)
            ->first();

        $ratingMap[(string)$i] = $percentileUser;
    }

    public function handle(): void
    {
        $k = Player::query()->whereNotNull('rating')->count();

        $ratingMap = [];

        for ($i = 1; $i < 100; $i++) {
            $this->determinePercentile($i, $k, $ratingMap);
        }

        collect([99.25, 99.5, 99.75, 99.9, 99.99, 99.999])->each(
            function ($i) use ($k, &$ratingMap) {
                $this->determinePercentile($i, $k, $ratingMap);
            }
        );

        EloSnapshot::query()->create([
            'date'    => Carbon::now(),
            'buckets' => json_encode($ratingMap),
            'type'    => EloSnapshot::TYPE_PERCENTILE,
        ]);
    }
}
