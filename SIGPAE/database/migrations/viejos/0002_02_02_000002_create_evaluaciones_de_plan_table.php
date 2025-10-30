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
        Schema::create('evaluaciones_de_plan', function (Blueprint $table) {
            $table->id();
            $table->string('criterios');
            $table->string('observaciones')->nullable();
            $table->string('conclusiones');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_de_plan');
    }
};
