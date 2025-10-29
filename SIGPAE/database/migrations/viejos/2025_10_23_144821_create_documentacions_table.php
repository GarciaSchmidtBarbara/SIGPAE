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
        Schema::create('documentacions', function (Blueprint $table) {
            $table->id('id_documentacion'); // Clave primaria personalizada
            $table->foreignId('fk_id_profesional')->constrained()->nullOnDelete();;
            $table->foreignId('fk_id_alumno')->constrained()->cascadeOnDelete();
            $table->foreignId('fk_id_plan')->constrained()->cascadeOnDelete();
            $table->foreignId('fk_id_intervencion')->constrained()->cascadeOnDelete();
            $table->foreignId('fk_id_evaluacion')->constrained()->cascadeOnDelete();

            $table->string('nombre');
            $table->enum('tipo_documento', ['plan_de_accion', 'alumno', 'intervencion', 'evaluacion']);
            $table->enum('formato', ['doc', 'docx', 'jpg', 'png', 'pdf', 'xls', 'xlsx']);
            $table->boolean('disponible_presencial')->default(false);
            $table->integer('tamanio_archivo')->nullable();
            $table->string('ruta_archivo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentacions');
    }
};
