<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otros_asistentes_i', function (Blueprint $table) {

            //revisado
            $table->id('id_otro_asistente_i');

            $table->string('nombre');
            $table->string('apellido');
            $table->string('descripcion');
            
            //revisado
            $table->foreignId('fk_id_intervencion')
                ->constrained('intervenciones', 'id_intervencion')
                ->onUpdate('cascade')
                ->onDelete('cascade');
             
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otros_asistentes_i');
    }
};
