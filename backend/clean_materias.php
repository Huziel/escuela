<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$materias = DB::table('materias')->whereIn('nombre', ['Inglés I', 'Matemáticas I'])->get();
$keepIds = $materias->pluck('id')->toArray();

echo "Conservando IDs: " . implode(', ', $keepIds) . "\n";
foreach ($materias as $m) {
    echo "  - ID {$m->id}: {$m->nombre}\n";
}

if (empty($keepIds)) {
    echo "\nNo se encontraron materias. Buscando similares...\n";
    $all = DB::table('materias')->select('id', 'nombre')->get();
    foreach ($all as $m) {
        echo "  ID {$m->id}: {$m->nombre}\n";
    }
    exit;
}

// Update related tables
DB::table('horarios')->whereNotIn('materia_id', $keepIds)->update(['materia_id' => null]);
DB::table('docente_materia')->whereNotIn('materia_id', $keepIds)->delete();
DB::table('calificaciones')->whereNotIn('materia_id', $keepIds)->update(['materia_id' => null]);

$deleted = DB::table('materias')->whereNotIn('id', $keepIds)->delete();
echo "Materias eliminadas: {$deleted}\n";
