<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->index('rating');
            $table->index('moving_rating');
            $table->index('no_move_rating');
            $table->index('nmpz_rating');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex('players_rating_index');
            $table->dropIndex('players_moving_rating_index');
            $table->dropIndex('players_no_move_rating_index');
            $table->dropIndex('players_nmpz_rating_index');
        });
    }
};
