<?php

namespace Database\Seeders;

use App\Models\Persona;
use App\Models\Profesional;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
       $this->call(BaseInstitucionalSeeder::class);
    }
}
