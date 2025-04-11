<?php

use App\Http\Controllers\SearchPlayerController;
use App\Models\EloSnapshot;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

const RAW = 'date IN (
        SELECT MAX(date)
        FROM elo_snapshots
        WHERE gamemode = "solo"
        GROUP BY DATE(date)
    )';

Route::get('/', function () {
    $soloSnapshots = EloSnapshot::query()
        ->where('gamemode', 'solo')
        ->whereRaw(RAW)
        ->orderByDesc('date')
        ->limit(10)
        ->get();

    $teamSnapshots = EloSnapshot::query()
        ->where('gamemode', 'team')
        ->whereRaw(RAW)
        ->orderByDesc('date')
        ->limit(10)
        ->get();

    return Inertia::render('EloSnapshotGraph', [
        'solo_snapshots' => $soloSnapshots->map(fn($snapshot) => [
            'date'    => $snapshot->date,
            'buckets' => json_decode($snapshot->buckets, true),
            'n'       => $snapshot->n,
        ])->toArray(),
        'team_snapshots' => $teamSnapshots->map(fn($snapshot) => [
            'date'    => $snapshot->date,
            'buckets' => json_decode($snapshot->buckets, true),
            'n'       => $snapshot->n,
        ])->toArray(),
    ]);
});


Route::prefix('players')->group(function () {
    Route::get('search', SearchPlayerController::class);
});
