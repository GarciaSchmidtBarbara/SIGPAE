<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            if (! Schema::hasColumn('eventos', 'es_derivacion_externa')) {
                $table->boolean('es_derivacion_externa')->default(false)->after('notas');
            }
            if (! Schema::hasColumn('eventos', 'profesional_tratante')) {
                $table->unsignedBigInteger('profesional_tratante')->nullable()->after('es_derivacion_externa');
                // add foreign key if profesionales table exists
                try {
                    $table->foreign('profesional_tratante')->references('id_profesional')->on('profesionales')->onDelete('set null');
                } catch (\Throwable $e) {
                    // ignore if constraint can't be created now
                }
            }
            if (! Schema::hasColumn('eventos', 'periodo_recordatorio')) {
                $table->integer('periodo_recordatorio')->nullable()->after('profesional_tratante');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            if (Schema::hasColumn('eventos', 'periodo_recordatorio')) {
                $table->dropColumn('periodo_recordatorio');
            }
            if (Schema::hasColumn('eventos', 'profesional_tratante')) {
                // drop fk if exists
                try {
                    $table->dropForeign(['profesional_tratante']);
                } catch (\Throwable $e) {}
                $table->dropColumn('profesional_tratante');
            }
            if (Schema::hasColumn('eventos', 'es_derivacion_externa')) {
                $table->dropColumn('es_derivacion_externa');
            }
        });
    }
};
