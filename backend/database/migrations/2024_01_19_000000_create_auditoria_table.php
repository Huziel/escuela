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
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('usuario_nombre', 200)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('accion', 50);
            $table->string('tabla', 100)->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('registro_anterior')->nullable();
            $table->json('registro_nuevo')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
