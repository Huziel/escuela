<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::table('docente_materia')->insertOrIgnore([
    ['docente_id' => 16, 'materia_id' => 1],
    ['docente_id' => 16, 'materia_id' => 2],
]);

echo "Julio ahora tiene Matemáticas I e Inglés I asignadas.\n";

$m = DB::table('materias')->whereIn('id', [1,2])->get();
foreach ($m as $mat) {
    echo "  ID {$mat->id}: {$mat->nombre} (especialidad_id: {$mat->especialidad_id})\n";
}
