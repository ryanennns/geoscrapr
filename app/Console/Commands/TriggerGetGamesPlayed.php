<?php

namespace App\Console\Commands;

use App\Jobs\GetGamesPlayed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TriggerGetGamesPlayed extends Command
{
    protected $signature = 'app:trigger-get-games-played';

    protected $description = 'Command description';

    public function handle(): void
    {
        GetGamesPlayed::dispatch();
    }
}
