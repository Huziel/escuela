<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$horarios = DB::table('horarios')->select('docente_id', 'grupo_id')->distinct()->get();
$count = 0;
foreach ($horarios as $h) {
    if ($h->docente_id && $h->grupo_id) {
        DB::table('docente_grupo')->insertOrIgnore([
            'docente_id' => $h->docente_id,
            'grupo_id' => $h->grupo_id,
        ]);
        $count++;
    }
}
echo "Sincronizados {$count} registros docente_grupo desde horarios.\n";
