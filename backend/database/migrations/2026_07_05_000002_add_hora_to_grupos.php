<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            if (!Schema::hasColumn('grupos', 'hora_inicio')) {
                $table->time('hora_inicio')->nullable()->after('capacidad');
            }
            if (!Schema::hasColumn('grupos', 'hora_fin')) {
                $table->time('hora_fin')->nullable()->after('hora_inicio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropColumn(['hora_inicio', 'hora_fin']);
        });
    }
};
