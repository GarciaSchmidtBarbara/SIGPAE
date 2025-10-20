<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backup any rows that reference profesional_id before dropping the column
        $backupTable = 'sessions_backup_before_remove_profesional_' . date('Ymd_His');
        DB::statement("CREATE TABLE IF NOT EXISTS \"$backupTable\" AS TABLE sessions WITH NO DATA;");
        DB::statement("INSERT INTO \"$backupTable\" SELECT * FROM sessions WHERE profesional_id IS NOT NULL;");

        Schema::table('sessions', function (Blueprint $table) {
            // Drop foreign key constraint and column if present
            if (Schema::hasColumn('sessions', 'profesional_id')) {
                // Try drop constrained foreign id (works on Laravel 8+)
                try {
                    $table->dropConstrainedForeignId('profesional_id');
                } catch (\Throwable $e) {
                    // If dropConstrainedForeignId isn't available or FK name differs,
                    // try to drop column directly (it will also drop constraint)
                    $table->dropColumn('profesional_id');
                }
            }
        });

        // Ensure user_id exists and is constrained to profesionales.id_profesional
        Schema::table('sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('sessions', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('last_activity')
                      ->constrained('profesionales', 'id_profesional')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        // In down, we will recreate profesional_id as nullable unsignedBigInteger
        Schema::table('sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('sessions', 'profesional_id')) {
                $table->unsignedBigInteger('profesional_id')->nullable()->after('id')->index();
            }
        });

        // We do not automatically drop the backup table in down() to avoid data loss.
    }
};
