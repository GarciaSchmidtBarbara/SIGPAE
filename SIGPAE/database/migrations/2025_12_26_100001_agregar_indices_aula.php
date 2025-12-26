<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarIndicesAula extends Migration
{
    public function up()
    {
        //Índice en la FK de alumnos hacia aulas, o sea útil para buscar alumnos de un aula 
        //especifica
        Schema::table('alumnos', function (Blueprint $table) {
            $table->index('fk_id_aula', 'alumnos_fk_id_aula_idx');
        });

        //Índice compuesto para búsquedas por curso+division en la tabla aulas
        Schema::table('aulas', function (Blueprint $table) {
            $table->index(['curso', 'division'], 'aulas_curso_division_idx');
        });
    }

    public function down()
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropIndex('alumnos_fk_id_aula_idx');
        });

        Schema::table('aulas', function (Blueprint $table) {
            $table->dropIndex('aulas_curso_division_idx');
        });
    }
}
