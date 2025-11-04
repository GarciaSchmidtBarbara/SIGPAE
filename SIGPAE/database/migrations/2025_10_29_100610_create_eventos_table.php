<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            //
            $table->id('id_evento');

            $table->enum('tipo_evento', ['BANDA', 'RG', 'RD', 'CITA_FAMILIAR']);
            $table->string('lugar');
            $table->date('fecha_hora');
            $table->string('notas')->nullable();
            
            // Datos adicionales para eventos de "Crear derivacion externa (en el Figma aparece)"
            /* en caso de que el evento sea de una derivacion externa se conusultaria si los
                atributos profesional_tratante y/o periodo_recordatorio son o no nulos */
            $table->string('profesional_tratante')->nullable();
            $table->integer('periodo_recordatorio') ->nullable(); //en semanas

            //revisado
            $table->foreignId('fk_id_profesional_creador')
                ->constrained('profesionales', 'id_profesional')
                ->onUpdate('cascade')
                ->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};