<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->integer('moving_rating')->nullable()->after('rating');
            $table->integer('no_move_rating')->nullable()->after('moving_rating');
            $table->integer('nmpz_rating')->nullable()->after('no_move_rating');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('moving_rating');
            $table->dropColumn('no_move_rating');
            $table->dropColumn('nmpz_rating');
        });
    }
};
