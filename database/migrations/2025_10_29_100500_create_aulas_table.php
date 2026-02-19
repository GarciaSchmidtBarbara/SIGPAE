<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //revisado
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
      Schema::dropIfExists('aulas');
    }
};