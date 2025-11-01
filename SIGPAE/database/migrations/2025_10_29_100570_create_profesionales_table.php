<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesionales', function (Blueprint $table) {
            $table->id('id_profesional');
            $table->enum('siglas', ['AS', 'AT', 'FN', 'PG', 'PS']);
            $table->string('profesion');
            $table->string('email')->unique();
            $table->string('telefono')->nullable();
            $table->string('usuario')->unique();
            $table->string('contrasenia');
            $table->foreignId('fk_id_persona')
                  ->constrained('personas', 'id_persona')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('profesionales');
    }
};
