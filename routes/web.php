<?php

use App\Http\Controllers\GetPlayersController;
use App\Http\Controllers\GetRatingChangeHistory;
use App\Http\Controllers\GetSnapshotForDate;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\SearchPlayerController;
use App\Http\Middleware\VerifyRequestReferer;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePageController::class);


Route::middleware([VerifyRequestReferer::class, 'throttle:60,1'])
    ->group(function () {
        Route::prefix('players')->group(function () {
            Route::get('/', GetPlayersController::class);
            Route::get('search', SearchPlayerController::class);
            Route::get('history/{user_id}', GetRatingChangeHistory::class);
        });

        Route::get('snapshots', GetSnapshotForDate::class);
    });
