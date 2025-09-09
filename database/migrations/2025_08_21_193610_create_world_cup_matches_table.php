<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('world_cup_matches', function (Blueprint $table) {
            $table->id();
            $table->string('round');

            $table->string('player_one_id')->nullable();
            $table->string('player_two_id')->nullable();
            $table->string('winner_id')->nullable();

            $table->foreign('player_one_id')->references('user_id')->on('players');
            $table->foreign('player_two_id')->references('user_id')->on('players');
            $table->foreign('winner_id')->references('user_id')->on('players');

            $table->boolean('is_live')->default(false);
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('link');

            $table->foreignId('next_match_id')->nullable()->constrained('world_cup_matches')->nullOnDelete();
            $table->foreignId('loser_match_id')->nullable()->constrained('world_cup_matches')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_cup_matches');
    }
};
