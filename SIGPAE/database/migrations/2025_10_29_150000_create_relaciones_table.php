<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
      //revisado
      Schema::create('participa_plan', function (Blueprint $table) {
            $table->foreignId('fk_id_profesional')
                  ->constrained('profesionales', 'id_profesional')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_plan_de_accion')
                  ->constrained('planes_de_accion', 'id_plan_de_accion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_profesional', 'fk_id_plan_de_accion']);
            $table->timestamps();
      });

      //revisado
      Schema::create('tiene_asignado', function (Blueprint $table) {
            $table->foreignId('fk_id_alumno')
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_plan_de_accion')
                  ->constrained('planes_de_accion', 'id_plan_de_accion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_alumno', 'fk_id_plan_de_accion']);      
            $table->timestamps();      
      });
      
      //revisado
      Schema::create('tiene_familiar', function (Blueprint $table) {

            $table->foreignId('fk_id_alumno')
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade') 
                  ->onDelete('cascade');
            
            $table->foreignId('fk_id_familiar')
                  ->constrained('familiares', 'id_familiar')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            $table->string('observaciones')->nullable();
            
            $table->primary(['fk_id_alumno', 'fk_id_familiar']);

            $table->timestamps();
      });

      //revisado
      Schema::create('es_hermano_de', function (Blueprint $table) {
            $table->foreignId('fk_id_alumno')
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_alumno_hermano') 
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            $table->boolean('activa')->default(true);

            $table->primary(['fk_id_alumno', 'fk_id_alumno_hermano']);  
            $table->timestamps();
      });

      //revisado
      Schema::create('acta_profesional', function (Blueprint $table) {
            $table->foreignId('fk_id_profesional')
                  ->constrained('profesionales', 'id_profesional')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_acta')
                  ->constrained('actas', 'id_acta')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_profesional', 'fk_id_acta']);
            $table->timestamps();
      });

      //revisado
      Schema::create('incluye', function (Blueprint $table) {
            $table->foreignId('fk_id_plan_de_accion')
                  ->constrained('planes_de_accion', 'id_plan_de_accion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_aula') 
                  ->constrained('aulas', 'id_aula')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_plan_de_accion', 'fk_id_aula']);  
            $table->timestamps();
      });

      //revisado
      Schema::create('tiene_aulas', function (Blueprint $table) {
            $table->foreignId('fk_id_evento')
                  ->constrained('eventos', 'id_evento')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_aula')
                  ->constrained('aulas', 'id_aula')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_evento', 'fk_id_aula']);
            $table->timestamps();
      });

      //revisado
      Schema::create('evento_alumno', function (Blueprint $table) {
            $table->foreignId('fk_id_evento') 
                  ->constrained('eventos', 'id_evento')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_alumno')
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_evento', 'fk_id_alumno']);  
            $table->timestamps();
      });

      //revisado
      Schema::create('intervencion_planilla', function (Blueprint $table) {
            $table->foreignId('fk_id_intervencion')
                  ->constrained('intervenciones', 'id_intervencion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_planilla') 
                  ->constrained('planillas', 'id_planilla')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_intervencion', 'fk_id_planilla']);  
            $table->timestamps();
      });

      //revisado
      Schema::create('intervencion_aula', function (Blueprint $table) {
            $table->foreignId('fk_id_intervencion') 
                  ->constrained('intervenciones', 'id_intervencion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_aula')
                  ->constrained('aulas', 'id_aula')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_intervencion', 'fk_id_aula']);  
            $table->timestamps();
      });

      //revisado
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

      //
      Schema::create('reune', function (Blueprint $table) {
            $table->foreignId('fk_id_profesional')
                  ->constrained('profesionales', 'id_profesional')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_id_intervencion')
                  ->constrained('intervenciones', 'id_intervencion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['fk_id_profesional', 'fk_id_intervencion']);
            $table->timestamps();
      });

      // Crea la extensión unaccent si no existe
      DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent;');
    }

    public function down(): void
    {
      Schema::dropIfExists('participa_plan');
      Schema::dropIfExists('tiene_asignado');
      Schema::dropIfExists('tiene_familiar');
      Schema::dropIfExists('es_hermano_de');
      Schema::dropIfExists('acta_profesional');
      Schema::dropIfExists('incluye');
      Schema::dropIfExists('tiene_aulas');
      Schema::dropIfExists('evento_alumno');
      Schema::dropIfExists('intervencion_planilla');
      Schema::dropIfExists('intervencion_aula');
      Schema::dropIfExists('intervencion_alumno');
      Schema::dropIfExists('reune');

      DB::statement('DROP EXTENSION IF EXISTS unaccent;');
    }
};
