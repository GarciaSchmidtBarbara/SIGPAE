<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id('id_alumno');
            $table->boolean('cud')->default(false);
            $table->integer('inasistencias')->default(0);
            $table->string('observaciones')->nullable();
            $table->string('antecedentes')->nullable();
            $table->string('intervenciones_externas')->nullable();
            $table->string('situacion_medica')->nullable();
            $table->string('actividades_extraescolares')->nullable();
            $table->string('situacion_escolar')->nullable();
            $table->string('situacion_familiar')->nullable();
            $table->string('situacion_socioeconomica')->nullable();
            $table->timestamps();
            $table->foreignId('fk_persona')
                  ->constrained('personas', 'id_persona')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('fk_aula')
                  ->constrained('aulas', 'id_aula')
                  ->onUpdate('cascade')
                  ->onDelete('set null');      
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
