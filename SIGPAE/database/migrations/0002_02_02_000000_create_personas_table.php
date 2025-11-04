<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            //revisado
            $table->id('id_persona');
            
            $table->biginteger('dni')->unique();
            $table->string('nombre');
            $table->string('apellido');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('domicilio')->nullable();
            $table->string('nacionalidad')->nullable();
            $table->boolean('activo')->default(true);   
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
