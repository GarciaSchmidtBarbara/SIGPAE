<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            //revisado
            $table->id('id_alumno');

            $table->boolean('cud')->default(false);
            $table->integer('inasistencias')->default(0);
            $table->text('observaciones')->nullable();
            $table->text('antecedentes')->nullable();
            $table->text('intervenciones_externas')->nullable();
            $table->text('actividades_extraescolares')->nullable();
            $table->text('situacion_escolar')->nullable();
            $table->text('situacion_medica')->nullable();
            $table->text('situacion_familiar')->nullable();
            $table->text('situacion_socioeconomica')->nullable();
            $table->timestamps();

            //revisado
            $table->foreignId('fk_id_persona')
                  ->constrained('personas', 'id_persona')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            //revisado
            $table->foreignId('fk_id_aula')
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
