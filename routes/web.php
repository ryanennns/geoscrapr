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
use App\Models\Player;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/world-cup', function () {
    return Inertia::render(
        'WorldCupView',
        ['players' => Player::query()->whereIn('user_id', [
            '57d301d409f2efcce834fc94',
            '601d17c1d565030001440b8d',
            '5de7a59044d2a42f78156b33',
            '603b1b0d5cdb1b0001bbf19e',
            '5bf491faaac55b998458ed9a',
            '5a973147afad0f2a68438531',
            '5e2e983722bbda85a40e9009',
            '57ebb537a52b273ab0162ed8',
            '5b51062a4010740f7cd91dd5',
            '5e5fcc1326bbda5284e824cf',
            '633a62ba560e8238dea97807',
            '5c03eed1b5b94ba700403005',
            '59d0b74bd8fe1d5b30651962',
            '635c171d190621fb60d8bb08',
            '55abc223ffb93d3658e4b76c',
            '5b4899f5b56fe41a1831bba4',
        ])->get()->toArray()]);
});

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
