<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id('id_notificacion');

            $table->enum('tipo', [
                'CONFIRMACION_ASISTENCIA',
                'CANCELACION_ASISTENCIA',
                'EVENTO_EDITADO',
                'EVENTO_BORRADO',
                'PLAN_EDITADO',
                'PLAN_BORRADO',
                'INTERVENCION_EDITADA',
                'INTERVENCION_BORRADA',
            ]);

            $table->text('mensaje');

            $table->boolean('leida')->default(false);

            //Referencia al recurso afectado (solo uno se llena por notificación).
            //SET NULL para que la notificación sobreviva si el recurso es eliminado,
            //permitiendo mostrar "fue eliminado" y deshabilitar el enlace.
            $table->unsignedBigInteger('fk_id_evento')->nullable();
            $table->foreign('fk_id_evento')
                ->references('id_evento')->on('eventos')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->unsignedBigInteger('fk_id_plan_de_accion')->nullable();
            $table->foreign('fk_id_plan_de_accion')
                ->references('id_plan_de_accion')->on('planes_de_accion')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->unsignedBigInteger('fk_id_intervencion')->nullable();
            $table->foreign('fk_id_intervencion')
                ->references('id_intervencion')->on('intervenciones')
                ->onUpdate('cascade')
                ->onDelete('set null');

            //Profesional que RECIBE la notificación
            $table->unsignedBigInteger('fk_id_profesional_destinatario');
            $table->foreign('fk_id_profesional_destinatario')
                ->references('id_profesional')->on('profesionales')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            //Profesional que ORIGINÓ la acción
            $table->unsignedBigInteger('fk_id_profesional_origen')->nullable();
            $table->foreign('fk_id_profesional_origen')
                ->references('id_profesional')->on('profesionales')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->timestamps();

            //Índice para consultas frecuentes: notificaciones no leídas de un profesional
            $table->index(['fk_id_profesional_destinatario', 'leida'], 'idx_notif_dest_leida');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
