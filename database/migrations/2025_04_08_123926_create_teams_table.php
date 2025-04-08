<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('team_id')->unique()->index();
            $table->string('name');
            $table->integer('rating');
            $table->string('player_a');
            $table->foreign('player_a')
                ->references('user_id')
                ->on('players')
                ->restrictOnDelete();
            $table->string('player_b');
            $table->foreign('player_b')
                ->references('user_id')
                ->on('players')
                ->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
