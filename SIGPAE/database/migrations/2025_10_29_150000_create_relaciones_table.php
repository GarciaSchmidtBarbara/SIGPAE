<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**Todas las tablas de relaciones:
es_invitado_a, participa_plan, tiene_familiar, es_hermano_de, acta_aula, tiene_aulas, tiene_profesional, intervencion_planilla, intervencion_aula, intervencion_alumno */

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiene_familiar', function (Blueprint $table) {
            $table->id('id_tiene_familiar');

            $table->foreignId('fk_alumno')
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade') 
                  ->onDelete('cascade'); //elimina el vinculo si se borra el alumno
            
            $table->foreignId('fk_familiar')
                  ->constrained('familiares', 'id_familiar')
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); //elimina el vinculo si se borra el familiar

                  //Esto no elimina los familiares ni los alumnos, solo elimina los vínculos en tiene_familiar. (eliminar el familiar en el modelo si ya no tiene mas vinculos)
            $table->timestamps();
        });

        Schema::create('tiene_asignado', function (Blueprint $table) {
            $table->id('id_tiene_asignado');
            $table->foreignId('fk_alumno')
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_plan')
                  ->constrained('planes_de_accion', 'id_plan')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

        Schema::create('tiene_aulas', function (Blueprint $table) {
            $table->foreignId('Fk_aulas')->constrained('aulas', 'id_aula')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('Fk_evento')->constrained('eventos', 'id_evento')->cascadeOnDelete()->cascadeOnUpdate();
            $table->primary(['Fk_aulas', 'Fk_evento']);
            $table->timestamps();
        });

        Schema::table('es_invitado_a', function (Blueprint $table) {
            $table->id('id_es_invitado');
            $table->boolean('confirmacion')->default(false);
            $table->boolean('asistio')->default(false);
            $table->foreignId('fk_profesional_invitado')
                  ->constrained('profesionales', 'id_profesional')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_evento')
                  ->constrained('eventos', 'id_evento')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('participa_plan', function (Blueprint $table) {
            $table->id('id_participa_plan');
            $table->foreignId('fk_profesional_participante')
                  ->constrained('profesionales', 'id_profesional')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_plan_participante')
                  ->constrained('planes_de_accion', 'id_plan')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->timestamps();
        });

    }

    public function down(): void
    {
      Schema::dropIfExists('tiene_familiar');
      Schema::dropIfExists('tiene_asignado');
      Schema::dropIfExists('tiene_aulas');
    }
};