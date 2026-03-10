<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdatePlayerRanks implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public function handle(): void
    {
        DB::transaction(function () {
            // DB::statement("SET LOCAL work_mem = '256MB'");
            DB::statement(<<<'SQL'
                WITH ranked AS (
                  SELECT
                    id,
                    ROW_NUMBER() OVER (ORDER BY rating DESC, id ASC) AS rank,
                    (100 - PERCENT_RANK() OVER (ORDER BY rating DESC) * 100)::numeric(5,2) AS percentile
                  FROM players
                  WHERE rating IS NOT NULL
                ),
                to_set AS (
                  SELECT id, rank, percentile FROM ranked
                  UNION ALL
                  SELECT id,
                         NULL::integer AS rank,
                         NULL::numeric(5,2) AS percentile
                  FROM players
                  WHERE rating IS NULL
                )
                UPDATE players p
                SET    rank = t.rank,
                       percentile = t.percentile
                FROM   to_set t
                WHERE  p.id = t.id
            SQL);
        });

        Log::info('Updated ranking and percentiles for all players.');
    }
}
