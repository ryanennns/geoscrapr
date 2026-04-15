<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class ClearApplicationCache implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Artisan::call('cache:clear');
    }
}
