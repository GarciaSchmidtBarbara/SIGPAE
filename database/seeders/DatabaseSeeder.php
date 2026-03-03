<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanillaSeeder::class,
            PersonaSeeder::class,
            ProfesionalSeeder::class,
            AulaSeeder::class,
            AlumnoSeeder::class,
            PlanDeAccionSeeder::class,
            IntervencionSeeder::class,
            EventoSeeder::class,
            NotificacionSeeder::class,
            DocumentoSeeder::class,
        ]);
    }
}
