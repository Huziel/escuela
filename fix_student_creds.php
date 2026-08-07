<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$count = 0;
$users = User::where('id', '!=', 1)->get();
foreach ($users as $u) {
    $a = $u->alumno;
    if ($a && $a->matricula) {
        $u->username = $a->matricula;
        if ($a->curp) $u->password = bcrypt($a->curp);
        $u->save();
        $count++;
    }
}
echo "$count students updated.\n";
echo "Sample: " . User::where('id', '!=', 1)->first()->username . "\n";
