<?php

namespace Database\Seeders;

use App\Models\PlanDeAccion;
use Illuminate\Database\Seeder;

class PlanDeAccionSeeder extends Seeder
{
    public function run(): void
    {
        PlanDeAccion::factory()->count(5)->individual()->create();

        PlanDeAccion::factory()->count(6)->grupal()->create();

        PlanDeAccion::factory()->count(2)->institucional()->create();
    }
}
