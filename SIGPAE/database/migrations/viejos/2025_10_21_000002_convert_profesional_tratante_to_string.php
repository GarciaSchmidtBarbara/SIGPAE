<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // If the column exists and is not text, create a temporary string column,
        // try to populate it from profesionales.usuario when possible, then replace.
        if (Schema::hasColumn('eventos', 'profesional_tratante')) {
            // create temporary column
            DB::statement("ALTER TABLE eventos ADD COLUMN IF NOT EXISTS profesional_tratante_str varchar(255) NULL;");

            // try to populate from profesionales table when original column is numeric
            try {
                DB::statement(
                    "UPDATE eventos e SET profesional_tratante_str = p.usuario FROM profesionales p WHERE e.profesional_tratante::text = p.id_profesional::text;"
                );
            } catch (\Throwable $e) {
                // ignore if cast fails or column not numeric
            }

            // if any rows still null, copy textual values if original was already text (defensive)
            try {
                DB::statement("UPDATE eventos SET profesional_tratante_str = profesional_tratante WHERE profesional_tratante_str IS NULL AND profesional_tratante IS NOT NULL;");
            } catch (\Throwable $e) {}

            // drop foreign key if exists and drop old column
            try {
                Schema::table('eventos', function (Blueprint $table) {
                    $table->dropForeign(['profesional_tratante']);
                });
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                DB::statement('ALTER TABLE eventos DROP COLUMN IF EXISTS profesional_tratante;');
            } catch (\Throwable $e) {}

            // rename new column
            DB::statement("ALTER TABLE eventos RENAME COLUMN profesional_tratante_str TO profesional_tratante;");
        } else {
            // column doesn't exist: just add it as string
            Schema::table('eventos', function (Blueprint $table) {
                $table->string('profesional_tratante')->nullable()->after('es_derivacion_externa');
            });
        }
    }

    public function down(): void
    {
        // reverse: try to change back to unsignedBigInteger (nullable). We won't attempt to map names back to ids.
        if (Schema::hasColumn('eventos', 'profesional_tratante')) {
            // create temporary numeric column
            Schema::table('eventos', function (Blueprint $table) {
                $table->unsignedBigInteger('profesional_tratante_old')->nullable()->after('es_derivacion_externa');
            });

            // we won't populate it automatically (can't reliably map names to ids). Then drop string and rename.
            DB::statement('ALTER TABLE eventos DROP COLUMN IF EXISTS profesional_tratante;');
            DB::statement('ALTER TABLE eventos RENAME COLUMN profesional_tratante_old TO profesional_tratante;');

            // try to add foreign key
            try {
                Schema::table('eventos', function (Blueprint $table) {
                    $table->foreign('profesional_tratante')->references('id_profesional')->on('profesionales')->onDelete('set null');
                });
            } catch (\Throwable $e) {}
        }
    }
};
