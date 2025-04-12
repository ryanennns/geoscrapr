<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('elo:singleplayer')->dailyAt('14:26');
Schedule::command('elo:teams')->dailyAt('14:46');
Schedule::command('snapshot:generate')->dailyAt('15:06');
