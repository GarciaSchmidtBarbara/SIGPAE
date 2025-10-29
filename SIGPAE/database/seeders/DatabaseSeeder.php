<?php

namespace Database\Seeders;

use App\Modules\Profesionales\Models\Profesional;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $this->call(BaseInstitucionalSeeder::class);
    }
}
