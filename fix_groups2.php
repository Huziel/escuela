<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Modules\Grupos\Models\Grupo;
use Illuminate\Support\Facades\DB;

// Real grupo names: digits + uppercase letters (e.g., "1A", "2BP", "1ACS")
// Student names: alphabetic with mixed case or all uppercase words
DB::statement('SET FOREIGN_KEY_CHECKS=0');

// Delete groups whose name doesn't match the pattern of a real grupo code
// Real codes: start with digit(s), followed by uppercase letter(s), e.g. "1A", "2BP", "1AC"
$deleted = Grupo::where('nombre', 'NOT REGEXP', '^[0-9]+[A-Z]+$')->delete();

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "Grupos eliminados: $deleted\n";
echo "Total grupos restantes: " . Grupo::count() . "\n\n";

echo "Grupos reales:\n";
Grupo::orderBy('nombre')->get()->each(function($g) {
    echo sprintf("%3d | %-10s | sem:%-2d | esp:%-2s | ciclo:%-8s | turno:%-10s\n",
        $g->id, $g->nombre, $g->semestre ?? 0,
        $g->especialidad_id ?? '-', $g->ciclo_escolar ?? '-', $g->turno ?? '-'
    );
});
