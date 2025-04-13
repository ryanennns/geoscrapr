<?php

namespace App\Http\Controllers;

use App\Models\EloSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HomePageController extends Controller
{
    const SOLO_RAW = 'date IN (
        SELECT MAX(date)
        FROM elo_snapshots
        WHERE gamemode = "solo"
        GROUP BY DATE(date)
    )';
    const TEAM_RAW = 'date IN (
        SELECT MAX(date)
        FROM elo_snapshots
        WHERE gamemode = "team"
        GROUP BY DATE(date)
    )';

    const MAX_SNAPSHOTS = 14;

    public function __invoke(): Response
    {
        $dates = EloSnapshot::query()->select(DB::raw('DATE(created_at) as date'))
            ->distinct()
            ->orderBy('date')
            ->pluck('date');

        $soloSnapshots = EloSnapshot::query()
            ->where('gamemode', 'solo')
            ->whereRaw(self::SOLO_RAW)
            ->orderByDesc('date')
            ->limit(self::MAX_SNAPSHOTS)
            ->get();

        $teamSnapshots = EloSnapshot::query()
            ->where('gamemode', 'team')
            ->whereRaw(self::TEAM_RAW)
            ->orderByDesc('date')
            ->limit(self::MAX_SNAPSHOTS)
            ->get();

        return Inertia::render('HomePage', [
            'solo_snapshots' => $soloSnapshots->map(fn($snapshot) => [
                'date'    => Carbon::parse($snapshot->date)->format('Y-m-d'),
                'buckets' => json_decode($snapshot->buckets, true),
                'n'       => $snapshot->n,
            ])->toArray(),
            'team_snapshots' => $teamSnapshots->map(fn($snapshot) => [
                'date'    => Carbon::parse($snapshot->date)->format('Y-m-d'),
                'buckets' => json_decode($snapshot->buckets, true),
                'n'       => $snapshot->n,
            ])->toArray(),
            'dates'          => $dates,
        ]);
    }
}
