<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$count = 0;
User::where('id', '!=', 1)->chunk(100, function ($users) use (&$count) {
    foreach ($users as $u) {
        $a = $u->alumno;
        if ($a && $a->curp) {
            $u->password = bcrypt($a->curp);
            $u->save();
            $count++;
        }
    }
    echo "$count processed...\n";
});
echo "Done. $count passwords set.\n";
