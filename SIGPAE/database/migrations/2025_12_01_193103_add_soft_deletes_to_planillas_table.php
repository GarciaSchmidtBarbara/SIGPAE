<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('planillas', function (Blueprint $table) {
            $table->softDeletes(); // Esto agrega la columna 'deleted_at'
        });
    }

    public function down(): void
    {
        Schema::table('planillas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
