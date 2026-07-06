<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ingles = DB::table('materias')->where('nombre', 'Inglés I')->orderBy('id')->first();
$mate = DB::table('materias')->where('nombre', 'Matemáticas I')->orderBy('id')->first();

if (!$ingles || !$mate) {
    echo "Error: no se encontraron materias\n";
    exit;
}

$keepIds = [$ingles->id, $mate->id];
echo "Conservando: ID {$ingles->id} = {$ingles->nombre}, ID {$mate->id} = {$mate->nombre}\n";

// Reassign all horarios with old materia_id to new ones
$oldIds = DB::table('materias')->whereNotIn('id', $keepIds)->pluck('id');
DB::table('horarios')->whereIn('materia_id', $oldIds)->update(['materia_id' => null]);

// Delete pivot entries
DB::table('docente_materia')->whereNotIn('materia_id', $keepIds)->delete();

// Nullify calificaciones
DB::table('calificaciones')->whereNotIn('materia_id', $keepIds)->update(['materia_id' => null]);

// Delete
$deleted = DB::table('materias')->whereNotIn('id', $keepIds)->delete();
echo "Eliminadas: {$deleted}\n";

$final = DB::table('materias')->get();
echo "\nMaterias finales:\n";
foreach ($final as $m) {
    echo "  ID {$m->id}: {$m->nombre}\n";
}
