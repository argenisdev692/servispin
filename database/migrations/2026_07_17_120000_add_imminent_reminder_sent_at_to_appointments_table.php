<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca de "recordatorio de 30 min ya enviado" (US-3, T039).
     *
     * El recordatorio inminente se programa cada 5 minutos, así que la misma cita
     * caería en varias ejecuciones dentro de su ventana. Sin esta marca, el
     * cliente recibiría el mismo email 6 veces. La marca hace el comando
     * idempotente. Aditiva y nullable: no toca las citas existentes.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('imminent_reminder_sent_at')->nullable()->after('meeting_link_failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('imminent_reminder_sent_at');
        });
    }
};
