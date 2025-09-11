<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FillRankColumns extends Command
{
    protected $signature = 'rankings:generate';

    protected $description = 'Populates the rank columns for players and teams based on their ELO ratings.';

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
