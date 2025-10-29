<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('evento_profesional')) {
            Schema::create('evento_profesional', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_evento');
                $table->unsignedBigInteger('id_profesional');

                $table->boolean('asistio')->default(false);
                $table->boolean('asistencia_confirmada')->default(false);

                $table->timestamps();

                $table->index('id_evento');
                $table->index('id_profesional');

                $table->foreign('id_evento')->references('id_evento')->on('eventos')->onDelete('cascade');
                $table->foreign('id_profesional')->references('id_profesional')->on('profesionales')->onDelete('cascade');

                $table->unique(['id_evento','id_profesional']);
            });
        } else {
            // tabla ya existe: agregar columnas/indexs/constraints faltantes de forma segura
            Schema::table('evento_profesional', function (Blueprint $table) {
                if (! Schema::hasColumn('evento_profesional', 'asistio')) {
                    $table->boolean('asistio')->default(false)->after('id_profesional');
                }
                if (! Schema::hasColumn('evento_profesional', 'asistencia_confirmada')) {
                    $table->boolean('asistencia_confirmada')->default(false)->after('asistio');
                }
                if (! Schema::hasColumn('evento_profesional', 'created_at')) {
                    $table->timestamps();
                }
                // intentar crear índices si no existen
                // Nota: Laravel no tiene Schema::hasIndex, usamos try/catch al crear
                try {
                    $table->index('id_evento');
                } catch (\Throwable $e) {}
                try {
                    $table->index('id_profesional');
                } catch (\Throwable $e) {}
                try {
                    $table->unique(['id_evento','id_profesional']);
                } catch (\Throwable $e) {}
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_profesional');
    }
};
