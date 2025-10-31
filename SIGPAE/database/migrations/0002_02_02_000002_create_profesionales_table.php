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
        Schema::create('profesionales', function (Blueprint $table) {
            $table->id('id_profesional');
            $table->enum('siglas', ['AS', 'AT', 'FN', 'PG', 'PS']);
            $table->string('profesion');
            $table->string('email')->unique();
            $table->string('telefono')->nullable();
            $table->string('usuario')->unique();
            $table->string('contrasenia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesionales');
    }
};
