<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            if (!Schema::hasColumn('alumnos', 'ciclo_escolar')) {
                $table->string('ciclo_escolar', 50)->nullable()->after('observaciones');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn('ciclo_escolar');
        });
    }
};
