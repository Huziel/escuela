<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
DB::table('docente_grupo')->delete();
DB::table('grupos')->delete();
DB::statement('ALTER TABLE grupos AUTO_INCREMENT = 1');
echo "Grupos: " . DB::table('grupos')->count() . "\n";
