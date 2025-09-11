<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Drop the composite index Laravel added for morphs()
        // Name follows: {table}_{type}_{id}_index
        Schema::table('rating_changes', function ($table) {
            $table->dropIndex('rating_changes_rateable_type_rateable_id_index');
        });

        // PostgreSQL: change BIGINT -> UUID
        // Keep valid UUIDs, null out anything else to avoid failure
        DB::statement(<<<'SQL'
            ALTER TABLE rating_changes
            ALTER COLUMN rateable_id TYPE uuid
            USING (
              CASE
                WHEN rateable_id::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'
                  THEN rateable_id::text::uuid
                ELSE NULL
              END
            )
        SQL);

        // Recreate the composite index
        Schema::table('rating_changes', function ($table) {
            $table->index(['rateable_type', 'rateable_id']);
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table('rating_changes', function ($table) {
            $table->dropIndex('rating_changes_rateable_type_rateable_id_index');
        });

        // Revert UUID -> BIGINT (invalid for real UUIDs; will null out non-numeric)
        DB::statement(<<<'SQL'
            ALTER TABLE rating_changes
            ALTER COLUMN rateable_id TYPE bigint
            USING (
              CASE
                WHEN rateable_id::text ~ '^[0-9]+$'
                  THEN rateable_id::text::bigint
                ELSE NULL
              END
            )
        SQL);

        Schema::table('rating_changes', function ($table) {
            $table->index(['rateable_type', 'rateable_id']);
        });
    }
};
