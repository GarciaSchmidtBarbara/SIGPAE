<?php

namespace Database\Seeders;

use App\Models\Intervencion;
use Illuminate\Database\Seeder;

class IntervencionSeeder extends Seeder
{
    public function run(): void
    {
        Intervencion::factory()->count(20)->create();
    }
}
