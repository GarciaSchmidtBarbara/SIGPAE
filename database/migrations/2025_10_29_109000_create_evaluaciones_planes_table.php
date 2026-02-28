<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_planes', function (Blueprint $table) {
            //revisado
            $table->id('id_evaluacion_plan_de_accion');

            $table->enum('tipo', ['parcial', 'final']);
            $table->text('observaciones')->nullable();
            $table->text('criterios');
            $table->text('conclusiones');
            //$table->datetime('fecha_hora');     este atributo lo maneja el $table->timestamps();
            $table->timestamps();

            //revisado
            $table->foreignId('fk_id_plan_de_accion')
                ->constrained('planes_de_accion', 'id_plan_de_accion')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_planes');
    }
};
