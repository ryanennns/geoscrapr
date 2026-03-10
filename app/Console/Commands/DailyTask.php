<?php

namespace App\Console\Commands;

use App\Jobs\CreateEloDistributionSnapshot;
use App\Jobs\CreateEloPercentileSnapshot;
use App\Jobs\UpdatePlayerRanks;
use App\Jobs\UpdatePlayerRatings;
use App\Jobs\UpdateTeamRatings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class DailyTask extends Command
{
    protected $signature = 'app:daily-tasks';

    protected $description = 'Command description';

    public function handle(): void
    {
        Bus::chain([
            new UpdatePlayerRatings(),
            new UpdateTeamRatings(),
            new CreateEloDistributionSnapshot(),
            new CreateEloPercentileSnapshot(),
            new UpdatePlayerRanks()
        ])->dispatch();
    }
}
