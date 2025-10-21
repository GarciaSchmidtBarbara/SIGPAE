<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Opcional: crear backup de las filas que vamos a modificar
$backupTable = 'sessions_backup_before_sync_' . date('Ymd_His');
DB::statement("CREATE TABLE IF NOT EXISTS \"$backupTable\" AS TABLE sessions WITH NO DATA;");
$rowsToBackup = DB::table('sessions')->whereNull('user_id')->whereNotNull('profesional_id')->count();
if ($rowsToBackup > 0) {
    DB::statement("INSERT INTO \"$backupTable\" SELECT * FROM sessions WHERE user_id IS NULL AND profesional_id IS NOT NULL;");
}

// Ejecutar actualización segura: solo copiar profesional_id -> user_id cuando user_id es NULL
$affected = DB::table('sessions')
    ->whereNull('user_id')
    ->whereNotNull('profesional_id')
    ->update(['user_id' => DB::raw('profesional_id')]);

echo "Backup table (if created): $backupTable\n";
echo "Rows backed up: $rowsToBackup\n";
echo "Rows updated (profesional_id -> user_id): $affected\n";

// Mostrar ejemplo de filas actualizadas (limit 10)
$examples = DB::table('sessions')->where('user_id','=',DB::raw('profesional_id'))->limit(10)->get();
echo "Examples (up to 10):\n";
echo json_encode($examples, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
