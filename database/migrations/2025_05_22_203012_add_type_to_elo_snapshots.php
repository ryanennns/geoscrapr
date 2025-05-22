<?php

use App\Models\EloSnapshot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elo_snapshots', function (Blueprint $table) {
            $table->enum('type', EloSnapshot::TYPES)
                ->default('elo_range');
        });
    }

    public function down(): void
    {
        Schema::table('elo_snapshots', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
