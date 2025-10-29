<?php
// Bootstrap minimal de Laravel para ejecutar código Eloquent fuera de artisan
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Necesario para usar Eloquent correctamente
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Modules\Personas\Models\Persona;
use App\Modules\Profesionales\Models\Profesional;

// Creamos o reutilizamos una persona de prueba
$persona = Persona::firstOrCreate(
    ['dni' => '99999999'],
    ['nombre' => 'Test', 'apellido' => 'User']
);

// Creamos o reutilizamos un profesional vinculado a la persona
$profesional = Profesional::firstOrCreate(
    ['usuario' => 'testuser'],
    [
        'profesion' => 'Docente',
        'telefono' => '12345678',
        'email' => 'test@example.com',
        // el cast 'password' => 'hashed' en el modelo se encargará de hashear
        'password' => 'secret',
        'fk_id_persona' => $persona->id_persona,
    ]
);

// Mostrar resultado como JSON
echo json_encode($profesional->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
