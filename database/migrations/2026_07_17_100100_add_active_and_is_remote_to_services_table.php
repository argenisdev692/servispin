<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade active e is_remote a services (plan §4, migración B).
     *
     * `active` arregla de paso un bug vivo (research #5): getServices() ejecuta
     * Service::where('active', true) contra una columna que no existe, así que
     * GET /appointments/services revienta hoy con un error SQL.
     *
     * OJO — cambio de comportamiento visible: con default true, todos los
     * servicios existentes quedan activos y esa ruta pasa de error 500 a
     * devolver la lista. Es la intención, pero no es un no-op.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('price');
            $table->boolean('is_remote')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['active', 'is_remote']);
        });
    }
};
