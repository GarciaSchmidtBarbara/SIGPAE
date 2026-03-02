<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        Alumno::factory()->count(10)->create();
    }
}
