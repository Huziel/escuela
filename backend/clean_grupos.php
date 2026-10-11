<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$grupos = DB::table('grupos')->select('id', 'nombre', 'semestre', 'turno')->get();

echo "=== TODOS LOS GRUPOS ===\n";
foreach ($grupos as $g) {
    echo "ID: {$g->id} | {$g->nombre} | Sem: {$g->semestre} | {$g->turno}\n";
}

$dosF = $grupos->firstWhere('nombre', '2F');
if (!$dosF) $dosF = $grupos->firstWhere('nombre', '2°F');

if ($dosF) {
    echo "\nCONSERVANDO: Grupo ID {$dosF->id} - {$dosF->nombre}\n";
    
    // Delete related data for other groups
    DB::table('horarios')->where('grupo_id', '!=', $dosF->id)->delete();
    DB::table('docente_grupo')->where('grupo_id', '!=', $dosF->id)->delete();
    DB::table('inscripciones')->where('grupo_id', '!=', $dosF->id)->delete();
    DB::table('calificaciones')->where('grupo_id', '!=', $dosF->id)->delete();
    DB::table('asistencias')->where('grupo_id', '!=', $dosF->id)->delete();
    
    // Unlink alumnos from groups being deleted
    DB::table('alumnos')->where('grupo_id', '!=', $dosF->id)->whereNotNull('grupo_id')->update(['grupo_id' => null]);
    
    $deleted = DB::table('grupos')->where('id', '!=', $dosF->id)->delete();
    echo "Grupos eliminados: {$deleted}\n";
} else {
    echo "\nNO SE ENCONTRO '2F'. Se muestran opciones:\n";
    foreach ($grupos as $g) {
        echo "  - ID {$g->id}: {$g->nombre}\n";
    }
    echo "\nElige uno manualmente.\n";
}
