<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_de_accion', function (Blueprint $table) {
            $table->id('id_plan_de_accion');
            $table->enum('estado_plan', ['ABIERTO', 'CERRADO'])->default('ABIERTO');
            $table->text('objetivos');
            $table->text('observaciones')->nullable();
            $table->text('acciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->enum('tipo_plan', ['INSTITUCIONAL', 'INDIVIDUAL', 'GRUPAL']);

            $table->foreignId('fk_id_profesional_generador')
                ->constrained('profesionales', 'id_profesional')
                ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_de_accion');
    }
};
