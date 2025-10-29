<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actas', function (Blueprint $table) {
            $table->id('id_acta');
            $table->enum('tipo_acta',['BANDA','REI','REIED']);
            $table->datatime('fecha_hora');
            //profesionales involucrados es una relacion N:N
            $table->json('otros_participante')->nullable();
            $table->text('temario')->nullable();
            $table->text('acuerdos')->nullable();
            $table->text('observaciones')->nullable();
            $table->date('fecha_proxima_reunion')->nullable();
            $table->foreignId('fk_id_aula')
                  ->references('id_aula')
                  ->on('aulas')
                  ->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('acta_pofesional', function (Blueprint $table) {
            $table->foreignId('fk_profesional')
                  ->references('id_profesional')
                  ->on('profesionales')
                  ->onDelete('set null');
            $table->foreingId('fk_acta')
                  ->references('id_acta')
                  ->on('actas')
                  ->onDelete('cascade');
            $table->primary(['fk_profesional', 'fk_acta']);
            $table->timestamps();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actas');
        Schema::dropIfExists('acta_profesional');
    }
};