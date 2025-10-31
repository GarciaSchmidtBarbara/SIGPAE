<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otros_asistentes_i', function (Blueprint $table) {
            $table->id('id_otro_asistente_i');
            $table->string('nombre_completo');
            $table->string('descripcion');
            
            $table->foreignId('fk_otro_asistente_a')->constrained('otros_asistentes_a', 'id_otro_asistente_a')->onUpdate('cascade'); //no lleva onDelete por que el profesional no se borra nunca
            $table->foreignId('fk_intervenciones')->constrained('intervenciones', 'id_intervencion')->onUpdate('cascade'); //no lleva onDelete por que el profesional no se borra nunca
           //no lleva onDelete por que el profesional no se borra nunca
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otros_asistentes_i');
    }
};
