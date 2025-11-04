<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planillas', function (Blueprint $table) {
            //revisado
            $table->id('id_planilla');
            $table->string('nombre_planilla');
            $table->enum('tipo_planilla',['MEDIAL','FINAL']);
            $table->year('anio');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planillas');
    }
};