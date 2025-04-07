<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('elo:singleplayer')->dailyAt('00:00');
Schedule::command('snapshot:generate')->dailyAt('12:00');
