<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up(): void
        {
            Schema::create('planillas', function (Blueprint $table) {
                $table->id('id_planilla');
                $table->string('nombre_planilla')->nullable(); 
                $table->string('tipo_planilla'); 
                $table->year('anio');
                $table->json('datos_planilla'); 
                $table->timestamps();
            });
        }

    public function down(): void
    {
        Schema::dropIfExists('planillas');
    }
};