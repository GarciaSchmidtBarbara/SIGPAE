<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones (subir).
     */
    public function up(): void
    {
        Schema::create('profesionales', function (Blueprint $table) {
            $table->id('id_profesional'); // Clave primaria personalizada
            //campos heredados de composer dump-autoloadir
            $table->string('nombre');
            $table->string('apellido');
            $table->string('dni')->unique(); 
            $table->string('domicilio')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('nacionalidad')->nullable();
            $table->timestamps();
        });

        // ---------------------------------------------
        // 2. TABLAS DE SUBTIPO (Herencia JTI)
        // ---------------------------------------------

        // 2.1 PROFESIONALES (Modelo de Autenticación)
        Schema::create('profesionales', function (Blueprint $table) {
            $table->id('id_profesional');
            $table->foreignId('fk_id_persona')->constrained('personas', 'id_persona')->onDelete('cascade');
            
            // Campos propios de Autenticación y Profesional
            $table->string('profesion');
            $table->string('telefono')->nullable();
            $table->string('usuario')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        
        // Agregamos la clave foránea creador_id a eventos (Ahora que profesionales existe)
        Schema::table('eventos', function (Blueprint $table) {
            $table->foreignId('creador_id')->after('id_evento')->constrained('profesionales', 'id_profesional')->onDelete('cascade');
        });

        // 2.2 ALUMNOS
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id('id_alumno');
            $table->foreignId('fk_id_persona')->constrained('personas', 'id_persona')->onDelete('cascade');
            
            // Campos que espera tu modelo Alumno.php
            $table->string('cud')->nullable();
            $table->integer('inasistencias')->default(0); 
            $table->string('grado_academico')->nullable(); 

            // Clave foránea al Aula (BelongsTo Aula)
            $table->foreignId('fk_id_aula')->nullable()->constrained('aulas', 'id_aula')->onDelete('set null');

            $table->timestamps();
        });

        // 2.3 FAMILIARES
        Schema::create('familiares', function (Blueprint $table) {
            $table->id('id_familiar');
            $table->foreignId('fk_id_persona')->constrained('personas', 'id_persona')->onDelete('cascade');
            
            $table->string('lugar_trabajo')->nullable();
            $table->string('telefono_trabajo')->nullable();
            $table->timestamps();
        });
        
        // 2.4 HERMANOS (Corregido a BelongsTo Alumno)
        Schema::create('hermanos', function (Blueprint $table) {
            $table->id('id_hermano');
            $table->foreignId('fk_id_persona')->constrained('personas', 'id_persona')->onDelete('cascade');
            
            // Clave foránea fk_id_alumno para la relación BelongsTo
            $table->foreignId('fk_id_alumno')
                  ->nullable() // Es opcional (0:1)
                  ->constrained('alumnos', 'id_alumno')
                  ->onDelete('set null');
            
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
        
        // ---------------------------------------------
        // 3. TABLAS PIVOTE (Relaciones Muchos a Muchos - M:M)
        // ---------------------------------------------

        // 3.1 ALUMNO-FAMILIAR
        Schema::create('alumno_familiar', function (Blueprint $table) {
            $table->foreignId('id_alumno')->constrained('alumnos', 'id_alumno')->onDelete('cascade');
            $table->foreignId('id_familiar')->constrained('familiares', 'id_familiar')->onDelete('cascade');
            $table->string('parentesco');
            $table->primary(['id_alumno', 'id_familiar']);
            $table->timestamps();
        });
        
        // 3.2 ALUMNO-EVENTO
        Schema::create('evento_alumno', function (Blueprint $table) {
            $table->foreignId('id_alumno')->constrained('alumnos', 'id_alumno')->onDelete('cascade');
            $table->foreignId('id_evento')->constrained('eventos', 'id_evento')->onDelete('cascade');
            $table->boolean('asistencia')->default(false);
            $table->primary(['id_alumno', 'id_evento']);
            $table->timestamps();
        });
        
        // 3.3 PROFESIONAL-EVENTO
        Schema::create('evento_profesional', function (Blueprint $table) {
            $table->foreignId('id_profesional')->constrained('profesionales', 'id_profesional')->onDelete('cascade');
            $table->foreignId('id_evento')->constrained('eventos', 'id_evento')->onDelete('cascade');
            $table->boolean('asistio')->default(false);
            $table->primary(['id_profesional', 'id_evento']);
            $table->timestamps();
        });


        // ---------------------------------------------
        // 4. TABLAS DE AUTENTICACIÓN Y SESIONES
        // ---------------------------------------------
        
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            // professional_id constrained ya existe
            $table->foreignId('profesional_id')->nullable()->index()->constrained('profesionales', 'id_profesional')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Revierte las migraciones (bajar).
     */
    public function down(): void
    {
        // Orden inverso: pivote > subtipo > infraestructura/base
        
        // 1. Pivote y Sesiones
        Schema::dropIfExists('evento_profesional');
        Schema::dropIfExists('evento_alumno');
        Schema::dropIfExists('alumno_hermano');
        Schema::dropIfExists('alumno_familiar');
        
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');

        // 2. Subtipos (que dependen de Persona y Aulas/Eventos)
        Schema::dropIfExists('hermanos');
        Schema::dropIfExists('familiares');
        Schema::dropIfExists('alumnos');
        
        // Eliminamos FKs en eventos antes de eliminar profesionales
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('creador_id');
        });
        
        Schema::dropIfExists('profesionales');
        
        // 3. Infraestructura y Base
        Schema::dropIfExists('eventos');
        Schema::dropIfExists('aulas');
        
        Schema::dropIfExists('personas');
    }
};