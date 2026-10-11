<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::table('plan_materia')->delete();
DB::table('planes_estudio')->delete();
DB::table('docente_especialidad')->delete();
DB::table('docente_materia')->delete();
DB::table('materias')->delete();
DB::table('especialidades')->delete();
DB::statement('ALTER TABLE materias AUTO_INCREMENT = 1');
DB::statement('ALTER TABLE especialidades AUTO_INCREMENT = 1');
DB::statement('ALTER TABLE planes_estudio AUTO_INCREMENT = 1');
echo "Especialidades: " . DB::table('especialidades')->count() . "\n";
echo "Materias: " . DB::table('materias')->count() . "\n";
echo "Planes: " . DB::table('planes_estudio')->count() . "\n";
echo "Listo.\n";
