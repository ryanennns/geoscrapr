<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_cup_matches', function (Blueprint $table) {
            $table->id();
            $table->string('round');

            $table->uuid('player_one_id')->nullable();
            $table->uuid('player_two_id')->nullable();
            $table->uuid('winner_id')->nullable();

            $table->foreign('player_one_id')->references('user_id')->on('players');
            $table->foreign('player_two_id')->references('user_id')->on('players');
            $table->foreign('winner_id')->references('user_id')->on('players');

            $table->boolean('is_live')->default(false);
            $table->timestamp('finished_at')->default(false);
            $table->string('link');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_cup_matches');
    }
};
