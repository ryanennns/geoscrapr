<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elo_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('gamemode', ['solo', 'team'])->default('solo');
            $table->json('buckets');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elo_snapshots');
    }
};
