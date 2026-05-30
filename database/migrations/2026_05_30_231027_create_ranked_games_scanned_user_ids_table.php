<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranked_games_scanned_user_ids', function (Blueprint $table) {
            $table->id();
            $table->jsonb('user_ids');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranked_games_scanned_user_ids');
    }
};
