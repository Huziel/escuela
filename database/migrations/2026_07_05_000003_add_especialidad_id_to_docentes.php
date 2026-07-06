<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            if (Schema::hasColumn('docentes', 'especialidad') && !Schema::hasColumn('docentes', 'especialidad_id')) {
                $table->foreignId('especialidad_id')->nullable()->after('rfc')->constrained('especialidades')->nullOnDelete();
            }
        });

        // Copy existing especialidad text values to especialidad_id if they match
        $docentes = DB::table('docentes')->whereNotNull('especialidad')->get();
        foreach ($docentes as $d) {
            $esp = DB::table('especialidades')->where('nombre', $d->especialidad)->first();
            if ($esp) {
                DB::table('docentes')->where('id', $d->id)->update(['especialidad_id' => $esp->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->dropForeign(['especialidad_id']);
            $table->dropColumn('especialidad_id');
        });
    }
};
