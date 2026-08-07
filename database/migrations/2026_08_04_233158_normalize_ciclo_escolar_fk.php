<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrate existing string ciclo_escolar to FK by matching nombre in ciclos_escolares
        // Since there's no data in alumnos currently (0 rows), just add the FK column
        // For grupos, there are 739 rows but most have ciclo_escolar as string

        // ALUMNOS: add ciclo_escolar_id FK, keep old varchar for now (we'll drop later)
        Schema::table('alumnos', function (Blueprint $table) {
            if (!Schema::hasColumn('alumnos', 'ciclo_escolar_id')) {
                $table->foreignId('ciclo_escolar_id')->nullable()->after('grupo_id')
                      ->constrained('ciclos_escolares')->nullOnDelete();
            }
        });

        // GRUPOS: add ciclo_escolar_id FK
        Schema::table('grupos', function (Blueprint $table) {
            if (!Schema::hasColumn('grupos', 'ciclo_escolar_id')) {
                $table->foreignId('ciclo_escolar_id')->nullable()->after('especialidad_id')
                      ->constrained('ciclos_escolares')->nullOnDelete();
            }
        });

        // Migrate existing grupos.ciclo_escolar string -> ciclos_escolares.id
        $ciclos = DB::table('ciclos_escolares')->pluck('id', 'nombre');
        foreach ($ciclos as $nombre => $id) {
            DB::table('grupos')->where('ciclo_escolar', $nombre)->update(['ciclo_escolar_id' => $id]);
            DB::table('alumnos')->where('ciclo_escolar', $nombre)->update(['ciclo_escolar_id' => $id]);
        }

        // CALIFICACIONES: add ciclo_escolar_id FK to replace periodo
        Schema::table('calificaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('calificaciones', 'ciclo_escolar_id')) {
                $table->foreignId('ciclo_escolar_id')->nullable()->after('grupo_id')
                      ->constrained('ciclos_escolares')->nullOnDelete();
            }
        });

        foreach ($ciclos as $nombre => $id) {
            DB::table('calificaciones')->where('periodo', $nombre)->update(['ciclo_escolar_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ciclo_escolar_id');
        });
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ciclo_escolar_id');
        });
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ciclo_escolar_id');
        });
    }
};
