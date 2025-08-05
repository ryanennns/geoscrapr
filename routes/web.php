<?php

use App\Http\Controllers\DownloadSqliteController;
use App\Http\Controllers\GetAvailableCountriesController;
use App\Http\Controllers\GetInDepthPlayerData;
use App\Http\Controllers\GetPlayerRatingChanges;
use App\Http\Controllers\GetPlayersController;
use App\Http\Controllers\GetRateable;
use App\Http\Controllers\GetSnapshotForDate;
use App\Http\Controllers\GetTeamRatingChanges;
use App\Http\Controllers\GetTeamsController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\SearchPlayerController;
use App\Http\Middleware\VerifyRequestReferer;
use App\Models\EloSnapshot;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePageController::class);


Route::middleware([VerifyRequestReferer::class, 'throttle:60,1'])
    ->group(function () {
        Route::prefix('players')->group(function () {
            Route::get('/', GetPlayersController::class);
            Route::get('search', SearchPlayerController::class);
            Route::get('history/{id}', GetPlayerRatingChanges::class);
            Route::get('/{id}/stats', GetInDepthPlayerData::class);
        });

        Route::get('snapshots', GetSnapshotForDate::class);
        Route::get('countries', GetAvailableCountriesController::class);

        Route::get('last-updated', function () {
            return ['date' => EloSnapshot::query()->select('created_at')->latest()->first()->created_at];
        });

        Route::get('rateables', GetRateable::class)->name('rateables');
    })
    ->group(function () {
        Route::prefix('teams')->group(function () {
            Route::get('/', GetTeamsController::class);
            Route::get('history/{id}', GetTeamRatingChanges::class);
        });
    });

Route::post('/download-sqlite', DownloadSqliteController::class);

Route::get('/search', \App\Http\Controllers\SearchController::class);
