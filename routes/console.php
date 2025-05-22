<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('elo:singleplayer')->dailyAt('04:00');
Schedule::command('elo:teams')->dailyAt('04:15');
Schedule::command('snapshot:generate')->dailyAt('04:30');
Schedule::command('snapshot:percentile')->dailyAt('04:35');
