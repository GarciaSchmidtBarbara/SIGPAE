<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            //revisado
            $table->id('id_documento');

            $table->string('nombre')->unique();
            $table->enum('contexto', ['perfil_alumno', 'plan_accion', 'intervencion', 'institucional'])->default('institucional');
            $table->enum('tipo_formato', ['DOCX', 'DOC', 'JPG', 'PNG', 'PDF', 'XLS', 'XLSX']);
            $table->boolean('disponible_presencial');
            $table->integer('tamanio_archivo');
            $table->string('ruta_archivo');
            

            //revisado
            $table->foreignId('fk_id_alumno')
                  ->nullable()
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            //revisado
            $table->foreignId('fk_id_evaluacion_plan_de_accion')
                  ->nullable()
                  ->constrained('evaluaciones_planes', 'id_evaluacion_plan_de_accion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
                  
            //revisado
            $table->foreignId('fk_id_evaluacion_intervencion_espontanea')
                  ->nullable()
                  ->constrained('evaluaciones_intervenciones_espontaneas', 'id_evaluacion_intervencion_espontanea')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            //revisado
            $table->foreignId('fk_id_profesional')
                  ->nullable()
                  ->constrained('profesionales', 'id_profesional')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            
            //revisado
            $table->foreignId('fk_id_plan_de_accion')
                  ->nullable()
                  ->constrained('planes_de_accion', 'id_plan_de_accion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            //revisado
            $table->foreignId('fk_id_intervencion')
                  ->nullable()
                  ->constrained('intervenciones', 'id_intervencion')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            // Usamos un nombre semántico en vez del created_at genérico de Laravel
            $table->timestamp('fecha_hora_carga')->useCurrent();
            // Sin updated_at (el documento no se edita, solo se sube y se borra)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');

    }
};
