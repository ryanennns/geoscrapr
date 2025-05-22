<?php

namespace App\Console\Commands;

use App\Models\EloSnapshot;
use App\Models\Player;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GeneratePercentileSnapshot extends Command
{
    protected $signature = 'snapshot:percentile';

    protected $description = 'Command description';

    private function determinePlayerPercentile($i, $k, &$ratingMap): void
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

    private function determineTeamPercentile($i, $k, &$ratingMap): void
    {
        $multiple = $i / 100;
        $index = round($k * $multiple);

        $percentileUser = Team::query()
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
        $playerK = Player::query()->whereNotNull('rating')->count();

        $soloRatingMap = [];

        for ($i = 1; $i < 100; $i++) {
            $this->determinePlayerPercentile($i, $playerK, $soloRatingMap);
        }

        $topPercentiles = [99.25, 99.5, 99.75, 99.9, 99.99, 99.999];

        collect($topPercentiles)->each(
            function ($i) use ($playerK, &$soloRatingMap) {
                $this->determinePlayerPercentile($i, $playerK, $soloRatingMap);
            }
        );

        EloSnapshot::query()->create([
            'date'    => Carbon::now(),
            'buckets' => json_encode($soloRatingMap),
            'type'    => EloSnapshot::TYPE_PERCENTILE,
            'n'       => $playerK,
        ]);

        $teamK = Team::query()->whereNotNull('rating')->count();
        $teamRatingMap = [];

        for ($i = 1; $i < 100; $i++) {
            $this->determineTeamPercentile($i, $teamK, $teamRatingMap);
        }

        collect($topPercentiles)->each(
            function ($i) use ($teamK, &$teamRatingMap) {
                $this->determineTeamPercentile($i, $teamK, $teamRatingMap);
            }
        );

        EloSnapshot::query()->create([
            'date'    => Carbon::now(),
            'buckets' => json_encode($teamRatingMap),
            'type'    => EloSnapshot::TYPE_PERCENTILE,
            'n'       => $teamK,
            'gamemode' => 'team',
        ]);
    }
}
