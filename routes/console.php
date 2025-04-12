<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('elo:singleplayer')->dailyAt('10:20');
Schedule::command('elo:teams')->dailyAt('10:40');
Schedule::command('snapshot:generate')->dailyAt('11:00');
