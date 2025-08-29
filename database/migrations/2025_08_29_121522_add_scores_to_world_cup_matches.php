<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('world_cup_matches', function (Blueprint $table) {
            $table->integer('player_one_score')->default(0);
            $table->integer('player_two_score')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('world_cup_matches', function (Blueprint $table) {
            $table->dropColumn(['player_one_score', 'player_two_score']);
        });
    }
};
