<?php
namespace Database\Seeders;

use App\Models\Noticia;
use Illuminate\Database\Seeder;

class NoticiaSeeder extends Seeder
{
    public function run(): void
    {
        $noticias = [
            ['titulo' => 'Inicio de Clases 2024-2025', 'contenido' => 'Les damos la bienvenida al nuevo ciclo escolar. Las clases inician el 26 de agosto.', 'fecha_publicacion' => '2024-08-20'],
            ['titulo' => 'Suspensión de Labores', 'contenido' => 'Se suspenden labores el próximo lunes 16 de septiembre por día festivo.', 'fecha_publicacion' => '2024-09-10'],
            ['titulo' => 'Entrega de Boletas', 'contenido' => 'La entrega de boletas del primer parcial será del 20 al 25 de octubre.', 'fecha_publicacion' => '2024-10-15'],
            ['titulo' => 'Evento Deportivo', 'contenido' => 'Torneo inter-grupos de fútbol el próximo viernes. ¡Participa!', 'fecha_publicacion' => '2024-11-01'],
            ['titulo' => 'Vacaciones de Invierno', 'contenido' => 'Las vacaciones de invierno serán del 23 de diciembre al 6 de enero.', 'fecha_publicacion' => '2024-12-15'],
        ];

        foreach ($noticias as $n) {
            Noticia::create($n + ['user_id' => 1, 'activo' => true]);
        }
    }
}
