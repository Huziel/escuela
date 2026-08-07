<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Modules\Grupos\Models\Grupo;

echo "Total grupos: " . Grupo::count() . "\n";
echo "\nUltimos 20 grupos:\n";
Grupo::latest()->take(20)->get()->each(function($g) {
    echo sprintf("%3d | %-25s | sem:%-2d | esp:%-2s | ciclo:%-8s | turno:%-10s | capacidad:%-3d\n",
        $g->id, substr($g->nombre ?? '', 0, 25), $g->semestre ?? 0,
        $g->especialidad_id ?? '-', $g->ciclo_escolar ?? '-', $g->turno ?? '-', $g->capacidad ?? 0
    );
});

// Count groups with names that look like student names (contain spaces between words)
$studentLike = Grupo::where('nombre', 'like', '% %')->count();
echo "\nGrupos con espacios en nombre (posibles alumnos): $studentLike\n";
