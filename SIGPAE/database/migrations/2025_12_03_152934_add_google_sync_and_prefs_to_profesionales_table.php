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
            $table->text('google_access_token')->nullable();
            $table->string('google_refresh_token',500)->nullable()
                ->comment('Solo se obtiene la primera vez, es necesario para mantener la sesión');
            $table->dateTime('google_token_expires_at')->nullable();

            //anticipación de notifiacion de un evento
            $table->integer('notificacion_anticipacion_minutos')->default(60)
                  ->comment('Minutos antes del evento para notificar al usuario.');
            
            //hora de envío del resumen diario 
            $table->dateTime('hora_envio_resumen_diario')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profesionales', function (Blueprint $table) {
            $table->dropColumn([
                'google_access_token',
                'google_refresh_token',
                'google_token_expires_at',
                'notificacion_anticipacion_minutos',
                'hora_envio_resumen_diario'
            ]);
        });
    }
};
