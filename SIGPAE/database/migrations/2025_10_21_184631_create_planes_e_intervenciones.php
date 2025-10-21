<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('planes_de_accion', function (Blueprint $table) {
            $table->id('id_plan');

            $table->string('estado');
            $table->string('tipo');
            $table->date('fecha_creacion');

            $table->unsignedBigInteger('fk_id_profesional_creador')->nullable();
            $table->unsignedBigInteger('fk_id_aula')->nullable();

            $table->foreign('fk_id_profesional_creador')
                  ->references('id_profesional')
                  ->on('profesionales')
                  ->onDelete('set null');

            $table->foreign('fk_id_aula')
                  ->references('id_aula')
                  ->on('aulas')
                  ->onDelete('set null');

            $table->timestamps();
        });

        Schema::create('intervenciones', function (Blueprint $table) {
            $table->id('id_intervencion');

            $table->date('fecha');
            $table->string('lugar');
            $table->string('tipo');

            $table->unsignedBigInteger('fk_profesional_creador')->nullable();
            $table->unsignedBigInteger('fk_id_plan')->nullable();
            $table->unsignedBigInteger('fk_id_evaluacion')->nullable();

            $table->foreign('fk_profesional_creador')
                  ->references('id_profesional')
                  ->on('profesionales')
                  ->onDelete('set null');

            $table->foreign('fk_id_plan')
                  ->references('id_plan')
                  ->on('planes_de_accion')
                  ->onDelete('set null');

            $table->foreign('fk_id_evaluacion')
                  ->references('id_evaluacion')
                  ->on('evaluaciones')
                  ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervenciones');
        Schema::dropIfExists('planes_de_accion');
    }
};
