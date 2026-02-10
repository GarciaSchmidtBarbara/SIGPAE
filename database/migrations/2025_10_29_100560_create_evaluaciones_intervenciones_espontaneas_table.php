<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_intervenciones_espontaneas', function (Blueprint $table) {
            //revisado
            $table->id('id_evaluacion_intervencion_espontanea');

            $table->string('criterios');
            $table->string('observaciones')->nullable();
            $table->string('conclusiones');
            // $table->datetime('fecha_hora_evaluacion'); lo maneja la columna created_at que esta en $table->timestamps();
            $table->timestamps();
        });   
    }

    public function down(): void
    {
      Schema::dropIfExists('evaluaciones_intervenciones_espontaneas');
    }
};