<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id('id_evento');
            $table->string('lugar');
            $table->date('fecha_hora');
            $table->enum('tipo_evento', ['BANDA', 'RG', 'RD', 'CITA_FAMILIAR']);
            $table->string('notas')->nullable();
            $table->foreignId('Fk_profesional_creador')->constrained('profesionales', 'id_profesional')->onUpdate('cascade');
            $table->boolean('es_derivacion_externa')->default(false);
            $table->string('profesional_tratante')->nullable();
            $table->integer('periodo_recordatorio') ->nullable(); //en semanas
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};