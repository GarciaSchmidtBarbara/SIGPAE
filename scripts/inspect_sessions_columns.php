<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'sessions' ORDER BY ordinal_position");

echo json_encode($columns, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
