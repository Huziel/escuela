<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fixed = DB::table('horarios')->whereNull('dia')->orWhere('dia', '')->update(['dia' => 'lunes']);
echo "Registros corregidos: {$fixed}\n";

$all = DB::table('horarios')->select('id', 'dia', 'hora_inicio', 'hora_fin', 'materia_id', 'docente_id')->get();
echo "Total horarios: " . count($all) . "\n";
foreach ($all as $h) {
    echo "  ID {$h->id}: dia={$h->dia}, hora={$h->hora_inicio}-{$h->hora_fin}, mat={$h->materia_id}, doc={$h->docente_id}\n";
}
