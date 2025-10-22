<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration{

    public function up(): void{ 
        // ---------------------------
        // Tablas principales
        // ---------------------------
        if (!Schema::hasTable('planes_de_accion')) {
            Schema::create('planes_de_accion', function (Blueprint $table) {
                $table->id('id_plan');
                $table->string('estado')->default('activo');
                $table->string('tipo');
                $table->date('fecha_creacion');

                $table->foreignId('fk_id_profesional_creador')
                    ->constrained('profesionales', 'id_profesional')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

                $table->foreignId('fk_id_aula')
                    ->nullable()
                    ->constrained('aulas', 'id_aula')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

                $table->timestamps();
                $table->check("(estado IN ('activo','cerrado'))");
            });
        }

        if (!Schema::hasTable('intervenciones')) {
            Schema::create('intervenciones', function (Blueprint $table) {
                $table->id('id_intervencion');
                $table->date('fecha');
                $table->string('lugar');
                $table->string('tipo');

                $table->foreignId('fk_profesional_creador')
                    ->constrained('profesionales', 'id_profesional')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

                $table->foreignId('fk_id_plan')
                    ->constrained('planes_de_accion', 'id_plan')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

                $table->foreignId('fk_id_evaluacion')
                    ->nullable()
                    ->constrained('evaluaciones', 'id_evaluacion')
                    ->onUpdate('cascade')
                    ->onDelete('set null');

                $table->timestamps();
            });
        }

        // ---------------------------
        // Tablas pivote (afuera de cualquier Blueprint)
        // ---------------------------
        if (!Schema::hasTable('responsables')) {
            Schema::create('responsables', function (Blueprint $table) {
                $table->foreignId('fk_id_plan')
                    ->constrained('planes_de_accion', 'id_plan')
                    ->cascadeOnDelete();

                $table->foreignId('fk_id_profesional_responsable')
                    ->constrained('profesionales', 'id_profesional')
                    ->cascadeOnDelete();

                $table->primary(['fk_id_plan','fk_id_profesional_responsable']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('plan_alumno')) {
            Schema::create('plan_alumno', function (Blueprint $table) {
                $table->foreignId('fk_id_plan')
                    ->constrained('planes_de_accion', 'id_plan')
                    ->cascadeOnDelete();

                $table->foreignId('fk_id_alumno')
                    ->constrained('alumnos', 'id_alumno')
                    ->cascadeOnDelete();

                $table->primary(['fk_id_plan','fk_id_alumno']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('intervencion_aula')) {
            Schema::create('intervencion_aula', function (Blueprint $table) {
                $table->foreignId('fk_id_intervencion')
                    ->constrained('intervenciones', 'id_intervencion')
                    ->cascadeOnDelete();

                $table->foreignId('fk_id_aula')
                    ->constrained('aulas', 'id_aula')
                    ->cascadeOnDelete();

                $table->primary(['fk_id_intervencion','fk_id_aula']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('intervencion_alumno')) {
            Schema::create('intervencion_alumno', function (Blueprint $table) {
                $table->foreignId('fk_id_intervencion')
                    ->constrained('intervenciones', 'id_intervencion')
                    ->cascadeOnDelete();

                $table->foreignId('fk_id_alumno')
                    ->constrained('alumnos', 'id_alumno')
                    ->cascadeOnDelete();

                $table->primary(['fk_id_intervencion','fk_id_alumno']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('documentacion_intervencion')) {
            Schema::create('documentacion_intervencion', function (Blueprint $table) {
                $table->foreignId('fk_id_intervencion')
                    ->constrained('intervenciones', 'id_intervencion')
                    ->cascadeOnDelete();

                $table->foreignId('fk_id_documentacion')
                    ->constrained('documentaciones', 'id_documentacion')
                    ->cascadeOnDelete();

                $table->primary(['fk_id_intervencion','fk_id_documentacion']);
                $table->timestamps();
            });
        }
    }    

    //Revierte las migraciones (bajar).
    public function down(): void
    {
        // Primero borrar tablas pivote
        if (Schema::hasTable('plan_alumno')) {
            Schema::dropIfExists('plan_alumno');
        }
        if (Schema::hasTable('responsables')) {
            Schema::dropIfExists('responsables');
        }
        if (Schema::hasTable('documentacion_intervencion')) {
            Schema::dropIfExists('documentacion_intervencion');
        }
        if (Schema::hasTable('intervencion_alumno')) {
            Schema::dropIfExists('intervencion_alumno');
        }
        if (Schema::hasTable('intervencion_aula')) {
            Schema::dropIfExists('intervencion_aula');
        }

        // Eliminar en orden inverso (primero dependientes)
        if (Schema::hasTable('intervenciones')) {
            Schema::dropIfExists('intervenciones');
        }

        if (Schema::hasTable('planes_de_accion')) {
            Schema::dropIfExists('planes_de_accion');
        }
    }
};
