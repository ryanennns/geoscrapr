<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillRankColumns extends Command
{
    protected $signature = 'rankings:generate';

    protected $description = 'Populates the rank columns for players and teams based on their ELO ratings.';

    public function handle(): void
    {
        DB::transaction(function () {
            DB::statement(<<<'SQL'
                WITH ranked AS (SELECT id,
                                       name,
                                       rating,
                                       ROW_NUMBER() OVER (ORDER BY rating DESC, id ASC)  AS rank,
                                       100 - PERCENT_RANK() OVER (ORDER BY rating DESC) * 100 AS percentile
                                FROM players
                                WHERE rating IS NOT NULL)
                UPDATE players
                set rank       = (select rank from ranked where ranked.id = players.id),
                    percentile = (select percentile from ranked where ranked.id = players.id);
                SQL
            );
            DB::statement("UPDATE players SET rank = NULL, percentile = NULL WHERE rating IS NULL");

        });
    }
}
