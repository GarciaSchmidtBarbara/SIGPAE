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
        Schema::table('tiene_familiar', function (Blueprint $table) {
        $table->boolean('activa')->default(true)->after('fk_id_familiar');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiene_familiar', function (Blueprint $table) {
        $table->dropColumn('activa');
    });
    }
};
