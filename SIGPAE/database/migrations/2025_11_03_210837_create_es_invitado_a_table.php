<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('es_invitado_a', function (Blueprint $table) {
            //revisado
            $table->id('id_es_invitado_a');

            $table->boolean('asistio')->default(false);
            $table->boolean('confirmacion')->default(false);
            $table->timestamps();

            //revisado
            $table->foreignId('fk_id_evento')
                ->constrained('eventos', 'id_evento')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            //revisado
            $table->foreignId('fk_id_profesional')
                ->constrained('profesionales', 'id_profesional')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('es_invitado_a');
    }
};
