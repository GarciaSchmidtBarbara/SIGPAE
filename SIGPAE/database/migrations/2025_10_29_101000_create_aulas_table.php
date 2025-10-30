<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**Todas las tablas de relaciones:
es_invitado_a, participa_plan, tiene_familiar, es_hermano_de, acta_aula, tiene_aulas, tiene_profesional, intervencion_planilla, intervencion_aula, intervencion_alumno */

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id('id_aula');

            $table->string('curso');
            $table->string('division');
            $table->unique(['curso', 'division']);
            $table->timestamps();

        });

        
    }

    public function down(): void
    {
      Schema::dropIfExists('aula');
    }
};