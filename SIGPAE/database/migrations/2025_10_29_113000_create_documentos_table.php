<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id('id_documento');
            $table->string('nombre');
            $table->enum('modalidad', ['PDF', 'WORD', 'TXT']);
            $table->boolean('disponible_presencial');
            $table->decimal('tamanio_archivo');
            $table->string('ruta_archivo');
            
            $table->foreignId('fk_alumno')
                  ->nullable()
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade');

            $table->foreignId('fk_evaluacion_plan')
                  ->nullable()
                  ->constrained('evaluaciones_planes', 'id_evaluacion_plan')
                  ->onUpdate('cascade');
                   
            $table->foreignId('fk_evaluacion_espontanea')
                  ->nullable()
                  ->constrained('evaluaciones_espontaneas', 'id_evaluacion_espontanea')
                  ->onUpdate('cascade');
            
            $table->foreignId('fk_profesional')
                  ->nullable()
                  ->constrained('profesionales', 'id_profesional')
                  ->onUpdate('cascade');
            
            $table->foreignId('fk_plan')
                  ->nullable()
                  ->constrained('planes_de_accion', 'id_plan')
                  ->onUpdate('cascade');
            
            $table->foreignId('fk_intervencion')
                  ->nullable()
                  ->constrained('intervenciones', 'id_intervencion')
                  ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');

    }
};
