<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otros_asistentes_a', function (Blueprint $table) {
            $table->id('id_otro_asistente_a');
            $table->string('funcion');
            $table->string('nombre');
            $table->foreignId('fk_acta')->constrained('actas', 'id_acta')->onUpdate('cascade'); //no lleva onDelete por que el profesional no se borra nunca
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otros_asistentes_a');
    }
};
