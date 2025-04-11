<?php

use App\Models\EloSnapshot;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $snapshot = EloSnapshot::query()->where('gamemode', 'solo')->latest('date')->first();

    return Inertia::render('EloSnapshotGraph', [
        'snapshot' => [
            'date' => $snapshot->date,
            'buckets' => json_decode($snapshot->buckets, true),
            'n' => $snapshot->n,
        ],
    ]);
});
