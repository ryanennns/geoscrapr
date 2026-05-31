<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->integer('ranked_duels_played')
                ->nullable()
                ->default(null);
            $table->integer('single_player_games_played')
                ->nullable()
                ->default(null);
            $table->integer('unranked_duels_played')
                ->nullable()
                ->default(null);
            $table->integer('ranked_team_duels_played')
                ->nullable()
                ->default(null);
            $table->integer('unranked_team_duels_played')
                ->nullable()
                ->default(null);
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'ranked_duels_played',
                'single_player_games_played',
                'unranked_duels_played',
                'ranked_team_duels_played',
                'unranked_team_duels_played',
            ]);
        });
    }
};
