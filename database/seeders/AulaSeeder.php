<?php

namespace Database\Seeders;

use App\Models\Aula;
use Illuminate\Database\Seeder;

class AulaSeeder extends Seeder
{
    public function run(): void
    {
       //Se crea una por una por la validacion de combinación única
        for ($i = 0; $i < 10; $i++) {
            Aula::factory()->create();
        }
    }
}
