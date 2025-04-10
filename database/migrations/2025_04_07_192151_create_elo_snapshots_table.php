<?php

use App\Models\EloSnapshot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elo_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->enum('gamemode', EloSnapshot::GAMEMODES)->default('solo');
            $table->json('buckets');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elo_snapshots');
    }
};
