<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rating_changes', function (Blueprint $table) {
            $table->enum('type', ['moving', 'no_move', 'nmpz', 'overall'])->default('overall');
        });
    }

    public function down(): void
    {
        Schema::table('rating_changes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
