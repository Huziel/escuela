<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreign('grupo_id')->references('id')->on('grupos')->nullOnDelete();
            $table->foreign('tutor_id')->references('id')->on('tutores')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropForeign(['grupo_id']);
            $table->dropForeign(['tutor_id']);
        });
    }
};
