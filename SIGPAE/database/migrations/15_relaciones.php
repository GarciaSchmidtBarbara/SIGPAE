<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiene_familiar', function (Blueprint $table) {
            $table->id('id_tiene_familiar');

            $table->foreignId('fk_alumno')
                  ->constrained('alumnos', 'id_alumno')
                  ->onUpdate('cascade') 
                  ->onDelete('cascade'); //elimina el vinculo si se borra el alumno
            
            $table->foreignId('fk_familiar')
                  ->constrained('familiares', 'id_familiar')
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); //elimina el vinculo si se borra el familiar

                  //Esto no elimina los familiares ni los alumnos, solo elimina los vínculos en tiene_familiar. (eliminar el familiar en el modelo si ya no tiene mas vinculos)
            $table->timestamps();
        });
    }

    public function down(): void
    {
      Schema::dropIfExists('tiene_familiar');
    }
};