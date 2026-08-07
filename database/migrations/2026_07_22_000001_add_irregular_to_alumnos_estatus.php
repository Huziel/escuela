<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE alumnos MODIFY COLUMN estatus ENUM('activo','inactivo','egresado','baja','bloqueado','irregular') NOT NULL DEFAULT 'activo'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE alumnos MODIFY COLUMN estatus ENUM('activo','inactivo','egresado','baja','bloqueado') NOT NULL DEFAULT 'activo'");
    }
};
