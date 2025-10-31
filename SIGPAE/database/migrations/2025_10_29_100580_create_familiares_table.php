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
        Schema::create('familiares', function (Blueprint $table) {
            $table->id();
            $table->string('telefono_personal')->nullable();
            $table->string('telefono_laboral')->nullable();
            $table->enum('parentesco', ["PADRE ",  "MADRE", "HERMANO", "TUTOR", "OTRO"]);
            $table->string('lugar_de_trabajo')->nullable();
            $table->string('observaciones')->nullable();
            $table->string('otro_parentesco')->nullable();
            $table->timestamps();
            $table->foreignId('fk_id_persona')
                  ->constrained('personas', 'id_persona')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('familiares');
    }
};
