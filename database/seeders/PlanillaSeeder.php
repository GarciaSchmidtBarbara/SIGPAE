<?php

namespace Database\Seeders;

use App\Models\Planilla;
use Illuminate\Database\Seeder;

class PlanillaSeeder extends Seeder
{
    public function run(): void
    {
        Planilla::factory()->count(15)->actaEquipo()->create();
        Planilla::factory()->count(10)->planillaMedial()->create();
        Planilla::factory()->count(5)->actaBanda()->create();
    }
}
