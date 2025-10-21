<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select("SELECT tablename FROM pg_tables WHERE tablename LIKE 'sessions_backup_before_remove_profesional_%' OR tablename LIKE 'sessions_backup_before_drop_profesional_%' ORDER BY tablename DESC");
if (empty($rows)) {
    echo "No backup table found\n";
    exit(0);
}
$latest = $rows[0]->tablename;
$count = DB::select("SELECT count(*) as cnt FROM \"$latest\"");
$cnt = $count[0]->cnt ?? 0;
echo json_encode(['backup_table' => $latest, 'rows' => (int)$cnt], JSON_PRETTY_PRINT) . PHP_EOL;
