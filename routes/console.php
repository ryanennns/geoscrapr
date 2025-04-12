<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('elo:singleplayer')->dailyAt('10:25');
Schedule::command('elo:teams')->dailyAt('10:45');
Schedule::command('snapshot:generate')->dailyAt('11:05');
