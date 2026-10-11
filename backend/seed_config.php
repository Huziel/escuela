<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::table('configuraciones')->updateOrInsert(
    ['clave' => 'max_docentes_por_grupo'],
    ['valor' => '5', 'tipo' => 'number', 'descripcion' => 'Maximo de docentes por grupo']
);
DB::table('configuraciones')->updateOrInsert(
    ['clave' => 'max_horas_diarias'],
    ['valor' => '8', 'tipo' => 'number', 'descripcion' => 'Maximo de horas diarias por docente']
);
echo "Config values seeded.\n";
