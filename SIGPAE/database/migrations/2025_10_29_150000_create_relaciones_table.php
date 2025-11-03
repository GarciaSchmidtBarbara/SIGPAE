<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



return new class extends Migration
{
    public function up(): void
    {
      Schema::create('tiene_familiar', function (Blueprint $table) {

            $table->foreignId('fk_id_alumno')
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade') 
                  ->onDelete('cascade'); //elimina el vinculo si se borra el alumno
            
            $table->foreignId('fk_id_familiar')
                  ->constrained('familiares', 'id_familiar')
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); //elimina el vinculo si se borra el familiar
            $table->primary(['fk_id_alumno', 'fk_id_familiar']);

                  //Esto no elimina los familiares ni los alumnos, solo elimina los vínculos en tiene_familiar. (eliminar el familiar en el modelo si ya no tiene mas vinculos)
            $table->timestamps();
      });

      Schema::create('tiene_asignado', function (Blueprint $table) {
            $table->foreignId('fk_id_alumno')
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_plan_de_accion')
                  ->constrained('planes_de_accion', 'id_plan_de_accion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_alumno', 'fk_id_plan']);      
            $table->timestamps();      
      });

      Schema::create('tiene_aulas', function (Blueprint $table) {
            $table->foreignId('Fk_aulas')
                ->constrained('aulas', 'id_aula')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('Fk_evento')
                ->constrained('eventos', 'id_evento')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->primary(['Fk_aulas', 'Fk_evento']);
            $table->timestamps();
      });

      Schema::create('acta_pofesional', function (Blueprint $table) {
            $table->foreignId('fk_profesional')
                  ->references('id_profesional')
                  ->on('profesionales')
                  ->onDelete('set null');
            $table->foreignId('fk_acta')
                  ->references('id_acta')
                  ->on('actas')
                  ->onDelete('cascade');
            $table->primary(['fk_profesional', 'fk_acta']);
            $table->timestamps();
      });

        Schema::create('es_invitado_a', function (Blueprint $table) {
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
            $table->primary(['fk_profesional_invitado', 'fk_evento']);
            $table->timestamps();
      });

        Schema::create('participa_plan', function (Blueprint $table) {
            $table->foreignId('fk_profesional_participante')
                  ->constrained('profesionales', 'id_profesional')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_plan_participante')
                  ->constrained('planes_de_accion', 'id_plan')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_profesional_participante', 'fk_plan_participante']);
            $table->timestamps();
      });

        Schema::create('intervencion_aula', function (Blueprint $table) {
            $table->foreignId('fk_id_aula')
                  ->constrained('aulas', 'id_aula')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_intervencion') 
                  ->constrained('intervenciones', 'id_intervencion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_aula', 'fk_id_intervencion']);  
            $table->timestamps();
      });
      
        Schema::create('intervencion_alumno', function (Blueprint $table) {
            $table->foreignId('fk_id_alumno')
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_intervencion') 
                  ->constrained('intervenciones', 'id_intervencion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_alumno', 'fk_id_intervencion']);  
            $table->timestamps();
      });
      Schema::create('intervencion_planilla', function (Blueprint $table) {
            $table->foreignId('fk_id_planilla')
                  ->constrained('planillas', 'id_planilla')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_intervencion') 
                  ->constrained('intervenciones', 'id_intervencion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_planilla', 'fk_id_intervencion']);  
            $table->timestamps();
      });

      Schema::create('es_hermano_de', function (Blueprint $table) {
            $table->foreignId('fk_id_alumno')
                  ->constrained('alumno', 'id_alumno')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_alumno_hermano') 
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_alumno', 'fk_id_alumno_hermano']);  
            $table->timestamps();
      });

      Schema::create('evento_alumno', function (Blueprint $table) {
            $table->foreignId('fk_id_alumno')
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_evento') 
                  ->constrained('eventos', 'id_evento')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_alumno', 'fk_id_evento']);  
            $table->timestamps();
      });
    }

    public function down(): void
    {
      Schema::dropIfExists('tiene_familiar');
      Schema::dropIfExists('tiene_asignado');
      Schema::dropIfExists('tiene_aulas');
      Schema::dropIfExists('acta_profesional');
      Schema::dropIfExists('intervencion_aula');
      Schema::dropIfExists('intervencion_alumno');  
      Schema::dropIfExists('intervencion_planilla'); 
      Schema::dropIfExists('es_invitado_a');
      Schema::dropIfExists('participa_plan');
      Schema::dropIfExists('es_hermano_de');
      Schema::dropIfExists('evento_alumno');
    }
};
