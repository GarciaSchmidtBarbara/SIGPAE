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
        Schema::table('profesionales', function (Blueprint $table) {
            $table->time('hora_envio_resumen_diario')->nullable();
            $table->integer('notification_anticipation_minutos')->nullable();
            $table->boolean('activo')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profesionales', function (Blueprint $table) {
            $table->dropColumn([
                'hora_envio_resumen_diario',
                'notification_anticipation_minutos',
                'activo'
            ]);
        });
    }
};
