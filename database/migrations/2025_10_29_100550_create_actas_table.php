<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //revisado
        Schema::create('actas', function (Blueprint $table) {
            $table->id('id_acta');
            
            $table->enum('tipo_acta',['BANDA','REI','REIED']);
            $table->datetime('fecha_hora');
            $table->text('temario')->nullable();
            $table->text('acuerdos')->nullable();
            $table->text('observaciones')->nullable();
            $table->datetime('fecha_proxima_reunion')->nullable();
            
            //revisado
            $table->foreignId('fk_id_aula')
                  ->constrained('aulas', 'id_aula')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actas');
    }
};