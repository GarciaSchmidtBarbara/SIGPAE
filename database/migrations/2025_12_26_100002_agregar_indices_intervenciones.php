<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarIndicesAIntervencionesYPivotes extends Migration
{
    public function up()
    {
        //Indices para separar en lo tipos (espontanea o programada) y para búsquedas por fecha/hora
        Schema::table('intervenciones', function (Blueprint $table) {
            $table->index('tipo_intervencion', 'intervenciones_tipo_intervencion_idx');
            $table->index('fecha_hora_intervencion', 'intervenciones_fecha_hora_idx');
        });

        //Índices para la relacion con las aulas y los alumnos
        Schema::table('intervencion_aula', function (Blueprint $table) {
            $table->index('fk_id_intervencion', 'intervencion_aula_fk_id_intervencion_idx');
            $table->index('fk_id_aula', 'intervencion_aula_fk_id_aula_idx');
        });

        Schema::table('intervencion_alumno', function (Blueprint $table) {
            $table->index('fk_id_intervencion', 'intervencion_alumno_fk_id_intervencion_idx');
            $table->index('fk_id_alumno', 'intervencion_alumno_fk_id_alumno_idx');
        });
    }

    public function down()
    {
        Schema::table('intervenciones', function (Blueprint $table) {
            $table->dropIndex('intervenciones_tipo_intervencion_idx');
            $table->dropIndex('intervenciones_fecha_hora_idx');
        });

        Schema::table('intervencion_aula', function (Blueprint $table) {
            $table->dropIndex('intervencion_aula_fk_id_intervencion_idx');
            $table->dropIndex('intervencion_aula_fk_id_aula_idx');
        });

        Schema::table('intervencion_alumno', function (Blueprint $table) {
            $table->dropIndex('intervencion_alumno_fk_id_intervencion_idx');
            $table->dropIndex('intervencion_alumno_fk_id_alumno_idx');
        });
    }
}