<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('sessions', function (Blueprint $table) {
      $table->string('id')->primary();                // ID único de la sesión
      $table->foreignId('user_id')->nullable();       // ID del usuario (opcional)
      $table->string('ip_address')->nullable();       // IP del cliente
      $table->text('user_agent')->nullable();         // Navegador y sistema
      $table->text('payload');                        // Datos serializados de la sesión
      $table->integer('last_activity');               // Timestamp de última actividad
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('sessions');
  }
};
