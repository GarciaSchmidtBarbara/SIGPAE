<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('planes_de_accion', function (Blueprint $table) {
            $table->id('id_plan_de_accion');
            $table->enum('estado_plan', ['ABIERTO', 'CERRADO'])->default('ABIERTO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_de_accion');
    }
};
