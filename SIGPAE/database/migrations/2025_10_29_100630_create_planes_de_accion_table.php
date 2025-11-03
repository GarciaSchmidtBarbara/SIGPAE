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
            $table->boolean('activo')->default(true);
            $table->string('objetivos');
            $table->string('observaciones')->nullable();
            $table->string('acciones')->nullable();
            $table->date('fecha_creacion');
            $table->enum('tipo_plan', ['INSTITUCIONAL', 'INDIVIDUAL', 'GRUPAL'])->default('INSTITUCIONAL');
            $table->foreignId('fk_id_profesional_creador')->constrained('profesionales', 'id_profesional')->onUpdate('cascade'); //no lleva onDelete por que el profesional no se borra nunca
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_de_accion');
    }
};
