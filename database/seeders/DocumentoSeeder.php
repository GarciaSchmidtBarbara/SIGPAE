<?php

namespace Database\Seeders;

use App\Models\Documento;
use App\Models\Profesional;
use Illuminate\Database\Seeder;

class DocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $profDoc = Profesional::first();

        Documento::factory()->count(3)->institucional()
            ->state(['fk_id_profesional' => $profDoc?->id_profesional])
            ->create();

        Documento::factory()->count(3)->perfilAlumno()
            ->state(['fk_id_profesional' => $profDoc?->id_profesional])
            ->create();

        Documento::factory()->count(2)->planAccion()
            ->state(['fk_id_profesional' => $profDoc?->id_profesional])
            ->create();

        Documento::factory()->count(2)->intervencion()
            ->state(['fk_id_profesional' => $profDoc?->id_profesional])
            ->create();
    }
}
