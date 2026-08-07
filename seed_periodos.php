<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::table('ciclos_escolares')->delete();
DB::table('ciclos_escolares')->insert([
    ['nombre' => '2024-1', 'fecha_inicio' => '2024-01-01', 'fecha_fin' => '2024-06-30', 'activo' => false],
    ['nombre' => '2024-2', 'fecha_inicio' => '2024-07-01', 'fecha_fin' => '2024-12-31', 'activo' => false],
    ['nombre' => '2025-1', 'fecha_inicio' => '2025-01-01', 'fecha_fin' => '2025-06-30', 'activo' => false],
    ['nombre' => '2025-2', 'fecha_inicio' => '2025-07-01', 'fecha_fin' => '2025-12-31', 'activo' => false],
    ['nombre' => '2026-1', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-06-30', 'activo' => true],
    ['nombre' => '2026-2', 'fecha_inicio' => '2026-07-01', 'fecha_fin' => '2026-12-31', 'activo' => false],
]);
echo "Periodos creados: " . DB::table('ciclos_escolares')->count() . "\n";
