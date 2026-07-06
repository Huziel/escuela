<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->string('edificio', 50)->nullable();
            $table->integer('capacidad')->default(30);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('grupos', function (Blueprint $table) {
            if (!Schema::hasColumn('grupos', 'aula_id')) {
                $table->foreignId('aula_id')->nullable()->after('tutor_id')->constrained('aulas')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropForeign(['aula_id']);
            $table->dropColumn('aula_id');
        });
        Schema::dropIfExists('aulas');
    }
};
