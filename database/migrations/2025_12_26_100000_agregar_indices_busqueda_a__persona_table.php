<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{   //UP, cuando se crea la migracion
    //Down cuando se revierte la migracion

    public function up()
    {
        //Busqueda de indices trigram requiere la extension pg_trgm, sirve para
        // acelerar las consultas LIKE con patrones '%algo%' entonces así podemos escribir
        //jos en la busqueda y devuelve resultados como "Jose", "San Jose", "Joserra", etc de 
        //forma más eficiente.

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');

        // Indice GIN (Generalized Inverted Index) para busquedas por nombre y apellido (insensible a mayusculas)
        // usando trigramas, aplica el lower para ignorar las mayús. El triagram lo que hace es lo dicho anteriormente, busca concordantes 
        //en subcadenas. Para tablas grandes de muchos alumnos mejora mucho el rendimiento de las consultas.
        DB::statement("CREATE INDEX IF NOT EXISTS personas_nombre_trgm_idx ON personas USING gin (lower(nombre) gin_trgm_ops);");
        DB::statement("CREATE INDEX IF NOT EXISTS personas_apellido_trgm_idx ON personas USING gin (lower(apellido) gin_trgm_ops);");

        //Índice B-tree para dni (igualdad/prefijo) 
        //Acelera busquedas exactas o por prefijo en el campo dni, como
        //es un arbol b es mucho más rapido para estas operaciones que un escaneo secuencial o el triagram
        Schema::table('personas', function (Blueprint $table) {
            $table->index('dni', 'personas_dni_idx');
        });
    }

    public function down()
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropIndex('personas_dni_idx');
        });

        DB::statement('DROP INDEX IF EXISTS personas_nombre_trgm_idx;');
        DB::statement('DROP INDEX IF EXISTS personas_apellido_trgm_idx;');
    } //esto tira para atrás los indices pero no la extensión de triagram por si decidimos usarla en otro lado
};
