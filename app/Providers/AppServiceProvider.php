<?php

namespace App\Providers;

use App\Models\Player;
use App\Models\Team;
use App\Observers\PlayerObserver;
use App\Observers\TeamObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Team::observe(TeamObserver::class);
        Player::observe(PlayerObserver::class);
    }
}
