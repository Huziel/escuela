<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->unique(['alumno_id', 'materia_id', 'periodo'], 'uq_calificaciones_alumno_materia_periodo');
        });
    }

    public function down(): void
    {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropUnique('uq_calificaciones_alumno_materia_periodo');
        });
    }
};
