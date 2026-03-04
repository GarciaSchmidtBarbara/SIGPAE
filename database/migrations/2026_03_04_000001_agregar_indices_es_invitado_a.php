<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('es_invitado_a', function (Blueprint $table) {
            //todos los eventos a los que el profesional X fue invitado, ordenados por fecha
            $table->index(
                ['fk_id_profesional', 'fk_id_evento'],
                'es_invitado_profesional_evento_idx'
            );

            $table->index(
                ['fk_id_evento'],
                'es_invitado_evento_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('es_invitado_a', function (Blueprint $table) {
            $table->dropIndex('es_invitado_profesional_evento_idx');
            $table->dropIndex('es_invitado_evento_idx');
        });
    }
};
