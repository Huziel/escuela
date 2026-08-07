<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('calificaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('calificaciones', 'asistencia_p1')) $table->integer('asistencia_p1')->nullable()->after('observaciones');
            if (!Schema::hasColumn('calificaciones', 'asistencia_p2')) $table->integer('asistencia_p2')->nullable()->after('asistencia_p1');
            if (!Schema::hasColumn('calificaciones', 'asistencia_p3')) $table->integer('asistencia_p3')->nullable()->after('asistencia_p2');
        });
    }
    public function down(): void {
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropColumn(['asistencia_p1','asistencia_p2','asistencia_p3']);
        });
    }
};
