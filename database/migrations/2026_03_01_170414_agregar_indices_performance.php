<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //notificaciones
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->index(
                ['fk_id_profesional_destinatario', 'leida', 'created_at'],
                'notif_destinatario_leida_fecha_idx'
            );
        });

        //Eventos
        Schema::table('eventos', function (Blueprint $table) {
            $table->index(
                ['fecha_hora'],
                'eventos_fecha_hora_idx'
            );
            $table->index(
                ['tipo_evento', 'fecha_hora'],
                'eventos_tipo_fecha_idx'
            );
            $table->index(
                ['fk_id_profesional_creador', 'fecha_hora'],
                'eventos_creador_fecha_idx'
            );
        });

        //Planes de accion
        Schema::table('planes_de_accion', function (Blueprint $table) {
            $table->index(
                ['deleted_at', 'estado_plan', 'created_at'],
                'planes_deleted_estado_fecha_idx'
            );
        });

        //Intervenciones (los índices simples ya existen, se agrega el compuesto)
        Schema::table('intervenciones', function (Blueprint $table) {
            $table->index(
                ['tipo_intervencion', 'fecha_hora_intervencion'],
                'intervenciones_tipo_fecha_compuesto_idx'
            );
        });

        //Planillas
        Schema::table('planillas', function (Blueprint $table) {
            $table->index(
                ['tipo_planilla', 'deleted_at', 'created_at'],
                'planillas_tipo_deleted_fecha_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropIndex('notif_destinatario_leida_fecha_idx');
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropIndex('eventos_fecha_hora_idx');
            $table->dropIndex('eventos_tipo_fecha_idx');
            $table->dropIndex('eventos_creador_fecha_idx');
        });

        Schema::table('planes_de_accion', function (Blueprint $table) {
            $table->dropIndex('planes_deleted_estado_fecha_idx');
        });

        Schema::table('intervenciones', function (Blueprint $table) {
            $table->dropIndex('intervenciones_tipo_fecha_compuesto_idx');
        });

        Schema::table('planillas', function (Blueprint $table) {
            $table->dropIndex('planillas_tipo_deleted_fecha_idx');
        });
    }
};
