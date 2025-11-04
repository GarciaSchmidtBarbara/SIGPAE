<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_de_accion', function (Blueprint $table) {
            //revisado
            $table->id('id_plan_de_accion');

            $table->enum('estado_plan', ['ABIERTO', 'CERRADO'])->default('ABIERTO');
            $table->text('objetivos');
            $table->text('observaciones')->nullable();
            //$table->datetime('fecha_hora');     este atributo lo maneja el $table->timestamps();
            $table->text('acciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->enum('tipo_plan', ['INSTITUCIONAL', 'INDIVIDUAL', 'GRUPAL']);

            //revisado
            $table->foreignId('fk_id_profesional_generador')
                ->constrained('profesionales', 'id_profesional')
                ->onUpdate('cascade'); //no lleva onDelete por que el profesional no se borra nunca

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_de_accion');
    }
};
