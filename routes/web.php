<?php

use App\Http\Controllers\SearchPlayerController;
use App\Models\EloSnapshot;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $soloSnapshot = EloSnapshot::query()->where('gamemode', 'solo')->latest('date')->first();
    $teamSnapshot = EloSnapshot::query()->where('gamemode', 'team')->latest('date')->first();

    return Inertia::render('EloSnapshotGraph', [
        'solo_snapshot' => [
            'date'    => $soloSnapshot->date,
            'buckets' => json_decode($soloSnapshot->buckets, true),
            'n'       => $soloSnapshot->n,
        ],
        'team_snapshot' => [
            'date'    => $teamSnapshot->date,
            'buckets' => json_decode($teamSnapshot->buckets, true),
            'n'       => $teamSnapshot->n,
        ]
    ]);
});


Route::prefix('players')->group(function () {
    Route::get('search', SearchPlayerController::class);
});
