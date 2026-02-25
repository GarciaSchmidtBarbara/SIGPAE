<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('otros_asistentes_a', function (Blueprint $table) {
            //revisado
            $table->id('id_otro_asistente_a');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('funcion');

            //revisado
            $table->foreignId('fk_id_acta')
                  ->constrained('actas', 'id_acta')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('otros_asistentes_a');
    }
};
