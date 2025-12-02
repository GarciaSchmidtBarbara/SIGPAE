<?php

namespace Database\Seeders;

use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Planilla; 


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        Planilla::factory()->count(15)->actaEquipo()->create();

        // 2. Crear 10 Planillas Mediales
        Planilla::factory()->count(10)->planillaMedial()->create();

        // 3. Crear 5 Actas de Banda
        Planilla::factory()->count(5)->actaBanda()->create();
       $this->call(BaseInstitucionalSeeder::class);
    }
}
