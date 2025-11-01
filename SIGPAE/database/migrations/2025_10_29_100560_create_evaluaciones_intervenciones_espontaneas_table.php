<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_espontaneas', function (Blueprint $table) {
            $table->id('id_evaluacion_espontanea');

            $table->string('criterios');
            $table->string('observaciones')->nullable();
            $table->string('conclusiones');
            $table->timestamps();

        });   
    }

    public function down(): void
    {
      Schema::dropIfExists('evaluaciones_espontaneas');
    }
};