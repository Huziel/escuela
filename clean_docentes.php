<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find Julio
$docentes = DB::table('docentes')
    ->join('users', 'docentes.user_id', '=', 'users.id')
    ->select('docentes.id as docente_id', 'users.id as user_id', 'users.nombre', 'users.apellido_paterno', 'users.apellido_materno')
    ->get();

echo "=== TODOS LOS DOCENTES ===\n";
foreach ($docentes as $d) {
    $full = trim("{$d->nombre} {$d->apellido_paterno} {$d->apellido_materno}");
    echo "Docente ID: {$d->docente_id} | User ID: {$d->user_id} | {$full}\n";
}

// Find Julio
$julio = null;
foreach ($docentes as $d) {
    $name = strtolower(trim("{$d->nombre} {$d->apellido_paterno} {$d->apellido_materno}"));
    $first = strtolower($d->nombre);
    $last = strtolower($d->apellido_paterno);
    if (str_contains($name, 'julio') || str_contains($first, 'julio') || str_contains($last, 'najera')) {
        $julio = $d;
        break;
    }
}

if ($julio) {
    $julioFull = trim("{$julio->nombre} {$julio->apellido_paterno} {$julio->apellido_materno}");
    echo "\nJULIO ENCONTRADO: Docente ID {$julio->docente_id}, User ID {$julio->user_id}, Nombre: {$julioFull}\n";
    
    // Delete related data first
    DB::table('docente_grupo')->where('docente_id', '!=', $julio->docente_id)->delete();
    DB::table('docente_materia')->where('docente_id', '!=', $julio->docente_id)->delete();
    DB::table('horarios')->where('docente_id', '!=', $julio->docente_id)->delete();
    DB::table('calificaciones')->where('docente_id', '!=', $julio->docente_id)->delete();
    
    // Get user IDs to delete (except Julio's user)
    $usersToDelete = DB::table('docentes')->where('id', '!=', $julio->docente_id)->pluck('user_id');
    
    // Delete docentes except Julio
    $deletedDocentes = DB::table('docentes')->where('id', '!=', $julio->docente_id)->delete();
    
    // Delete associated users
    $deletedUsers = DB::table('users')->whereIn('id', $usersToDelete)->delete();
    
    echo "Docentes eliminados: {$deletedDocentes}\n";
    echo "Usuarios eliminados: {$deletedUsers}\n";
    echo "\nSe conservó a {$julioFull}\n";
} else {
    echo "\nNO SE ENCONTRÓ A JULIO NAJERA\n";
}
