<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('intervenciones', function (Blueprint $table) {
            $table->id('id_intervencion');

            $table->dateTime('fecha_hora');
            $table->string('lugar');
            $table->enum('modalidad', ['PRESENCIAL', 'ONLINE', 'OTRA']);
            $table->string('otra_modalidad')->nullable();
            $table->string('temas_tratados')->nullable();
            $table->string('compromisos')->nullable();
            $table->string('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            
            $table->foreignId('fk_profesional_genera')
                  ->constrained('profesionales', 'id_profesional')
                  ->onUpdate('cascade');

            $table->foreignId('fk_pertenece_plan')
                  ->nullable()
                  ->constrained('planes_de_accion', 'id_plan')
                  ->onUpdate('cascade') 
                  ->onDelete('restrict');
            //DUDA: no deberia borrarse un plan si tiene intervenciones. Cierto?

            $table->foreignId('fk_evaluacion_espontanea')
                  ->nullable()
                  ->constrained('evaluaciones_intervenciones_espontaneas', 'id_evaluacion')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervenciones');

    }
};
