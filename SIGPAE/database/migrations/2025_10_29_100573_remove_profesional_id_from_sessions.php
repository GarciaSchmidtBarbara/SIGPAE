<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Elimina la columna profesional_id de sessions y agrega user_id vinculado a profesionales.
     * Realiza respaldo condicional si la columna existe.
     */
    public function up(): void
    {
        // ------------------------------------------------------------------
        // 1. RESPALDO CONDICIONAL DE DATOS (si existe profesional_id)
        // ------------------------------------------------------------------
        if (Schema::hasColumn('sessions', 'profesional_id')) {
            $backupTable = 'sessions_backup_before_remove_profesional_' . date('Ymd_His');

            // Crear tabla vacía con misma estructura
            DB::statement("CREATE TABLE IF NOT EXISTS \"$backupTable\" AS TABLE sessions WITH NO DATA;");

            // Insertar solo si la columna existe
            DB::statement("INSERT INTO \"$backupTable\" SELECT * FROM sessions WHERE profesional_id IS NOT NULL;");

            // ------------------------------------------------------------------
            // 2. ELIMINAR CLAVE FORÁNEA Y COLUMNA profesional_id
            // ------------------------------------------------------------------
            Schema::table('sessions', function (Blueprint $table) {
                try {
                    $table->dropConstrainedForeignId('profesional_id');
                } catch (\Throwable $e) {
                    $table->dropColumn('profesional_id');
                }
            });
        }

        // ------------------------------------------------------------------
        // 3. AGREGAR COLUMNA user_id VINCULADA A profesionales.id_profesional
        // ------------------------------------------------------------------
        Schema::table('sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('sessions', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('last_activity')
                      ->constrained('profesionales', 'id_profesional')
                      ->onDelete('cascade');
            }
        });
    }

    /**
     * Reversión segura: restaura profesional_id si no existe.
     * No elimina tabla de respaldo para preservar trazabilidad.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            if (Schema::hasColumn('sessions', 'user_id')) {
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Throwable $e) {
                    // Si falla, intentar eliminar solo la columna
                }
                
                try {
                    $table->dropColumn('user_id');
                } catch (\Throwable $e) {
                    // Columna ya eliminada
                }
            }
            
            if (! Schema::hasColumn('sessions', 'profesional_id')) {
                $table->unsignedBigInteger('profesional_id')->nullable()->after('id')->index();
            }
        });

        // Nota: no se elimina la tabla de respaldo para evitar pérdida de datos.
    }
};