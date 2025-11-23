<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('intervenciones', function (Blueprint $table) {
            //revisado
            $table->id('id_intervencion');

            $table->dateTime('fecha_hora_intervencion'); //fecha y hora de la intervencion, no la fecha de creacion del registro
            $table->string('lugar');
            $table->enum('modalidad', ['PRESENCIAL', 'ONLINE', 'OTRA']);
            $table->string('otra_modalidad')->nullable();
            $table->text('temas_tratados')->nullable();
            $table->text('compromisos')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->enum('tipo_intervencion', ['ESPONTANEA', 'PROGRAMADA']);
            $table->timestamps();

            $table->foreignId('fk_id_profesional_genera')
                  ->constrained('profesionales', 'id_profesional')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            
            $table->foreignId('fk_id_plan_de_accion')
                  ->nullable()
                  ->constrained('planes_de_accion', 'id_plan_de_accion')
                  ->onUpdate('cascade') 
                  ->onDelete('restrict');

            $table->foreignId('fk_id_evaluacion_intervencion_espontanea')
                  ->nullable()
                  ->constrained('evaluaciones_intervenciones_espontaneas', 'id_evaluacion_intervencion_espontanea')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervenciones');

    }
};
