<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_planes', function (Blueprint $table) {
            $table->id('id_evaluacion_plan');
            $table->date('fecha_evaluacion');
            $table->string('observaciones')->nullable();
            $table->string('criterios');
            $table->string('conclusiones');
            $table->timestamps();
            $table->foreignId('fk_id_plan')
                ->constrained('planes_de_accion', 'id_plan')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_planes');
    }
};
