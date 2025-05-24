<?php

namespace App\Http\Controllers;

use App\Models\EloSnapshot;
use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HomePageController extends Controller
{
    const MAX_SNAPSHOTS = 14;

    public function __invoke(): Response
    {
        $rangeDates = EloSnapshot::query()->select(DB::raw('DATE(created_at) as date'))
            ->where('type', EloSnapshot::TYPE_ELO_RANGE)
            ->distinct()
            ->orderBy('date')
            ->pluck('date');

        $percentileDates = EloSnapshot::query()->select(DB::raw('DATE(created_at) as date'))
            ->where('type', EloSnapshot::TYPE_PERCENTILE)
            ->distinct()
            ->orderBy('date')
            ->pluck('date');

        $soloRangeSnapshots = EloSnapshot::query()
            ->where('gamemode', 'solo')
            ->where('type', EloSnapshot::TYPE_ELO_RANGE)
            ->orderByDesc('date')
            ->limit(self::MAX_SNAPSHOTS)
            ->get();

        $teamRangeSnapshots = EloSnapshot::query()
            ->where('gamemode', 'team')
            ->where('type', EloSnapshot::TYPE_ELO_RANGE)
            ->orderByDesc('date')
            ->limit(self::MAX_SNAPSHOTS)
            ->get();

        $soloPercentileSnapshots = EloSnapshot::query()
            ->where('gamemode', 'solo')
            ->where('type', EloSnapshot::TYPE_PERCENTILE)
            ->orderByDesc('date')
            ->limit(self::MAX_SNAPSHOTS)
            ->get();

        $teamPercentileSnapshots = EloSnapshot::query()
            ->where('gamemode', 'team')
            ->where('type', EloSnapshot::TYPE_PERCENTILE)
            ->orderByDesc('date')
            ->limit(self::MAX_SNAPSHOTS)
            ->get();

        return Inertia::render('HomePage', [
            'solo_snapshots'            => $soloRangeSnapshots->map(fn($snapshot) => [
                'date'    => Carbon::parse($snapshot->date)->format('Y-m-d'),
                'buckets' => json_decode($snapshot->buckets, true),
                'n'       => $snapshot->n,
            ])->toArray(),
            'team_snapshots'            => $teamRangeSnapshots->map(fn($snapshot) => [
                'date'    => Carbon::parse($snapshot->date)->format('Y-m-d'),
                'buckets' => json_decode($snapshot->buckets, true),
                'n'       => $snapshot->n,
            ])->toArray(),
            'solo_percentile_snapshots' => $soloPercentileSnapshots->map(fn($snapshot) => [
                'date'    => Carbon::parse($snapshot->date)->format('Y-m-d'),
                'buckets' => json_decode($snapshot->buckets, true),
                'n'       => $snapshot->n,
            ])->toArray(),
            'team_percentile_snapshots' => $teamPercentileSnapshots->map(fn($snapshot) => [
                'date'    => Carbon::parse($snapshot->date)->format('Y-m-d'),
                'buckets' => json_decode($snapshot->buckets, true),
                'n'       => $snapshot->n,
            ])->toArray(),
            'range_dates'               => $rangeDates,
            'percentile_dates'          => $percentileDates,
            'leaderboard'               => Player::query()
                ->orderBy('rating', 'desc')
                ->limit(10)
                ->get(),
        ]);
    }
}
