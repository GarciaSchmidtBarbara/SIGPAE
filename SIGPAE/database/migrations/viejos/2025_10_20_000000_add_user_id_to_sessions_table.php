<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Añadimos user_id por compatibilidad con código / paquetes que esperan user_id
            if (! Schema::hasColumn('sessions', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('last_activity')
                      ->constrained('profesionales', 'id_profesional')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            if (Schema::hasColumn('sessions', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
