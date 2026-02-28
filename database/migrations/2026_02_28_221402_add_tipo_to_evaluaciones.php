<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluaciones_planes', function (Blueprint $table) {
            $table->enum('tipo', ['parcial', 'final'])
                  ->default('parcial')
                  ->after('conclusiones');
        });

        Schema::table('evaluaciones_intervenciones_espontaneas', function (Blueprint $table) {
            $table->enum('tipo', ['parcial', 'final'])
                  ->default('parcial')
                  ->after('conclusiones');
        });
    }

    public function down(): void
    {
        Schema::table('evaluaciones_planes', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::table('evaluaciones_intervenciones_espontaneas', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};