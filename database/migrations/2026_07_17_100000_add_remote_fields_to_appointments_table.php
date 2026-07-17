<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extiende appointments para soportar la modalidad remota (plan §4, migración A).
     *
     * Puramente aditiva: modality tiene default 'onsite', así que las citas
     * existentes no cambian de comportamiento.
     *
     * start_time/end_time NO se tocan: siguen persistiéndose en el huso de la
     * aplicación (Atlantic/Canary) igual que las presenciales. Guardar las
     * remotas en UTC dejaría dos convenciones en la misma columna y rompería la
     * detección de solapamiento (FR-7). Ver plan §9 R-5.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('modality', ['onsite', 'remote'])->default('onsite')->after('status');

            // Huso del cliente, solo para presentación (FR-6). El instante vive en start_time.
            $table->string('client_timezone', 64)->nullable()->after('modality');

            // Enlace de la videollamada (FR-8)
            $table->text('meeting_url')->nullable()->after('client_timezone');
            $table->string('meeting_provider', 32)->nullable()->after('meeting_url');

            // Evento en Google Calendar (FR-14): permiten editarlo/borrarlo después
            $table->string('google_event_id', 255)->nullable()->after('meeting_provider');
            $table->string('google_calendar_id', 255)->nullable()->after('google_event_id');

            // Marca "pega el enlace a mano" cuando el provider automático falla (FR-15)
            $table->timestamp('meeting_link_failed_at')->nullable()->after('google_calendar_id');

            // Declaración de pago. Nunca datos de tarjeta (FR-4).
            $table->enum('payment_status', ['unpaid', 'claimed', 'verified', 'rejected', 'refund_pending'])
                ->default('unpaid')
                ->after('meeting_link_failed_at');
            $table->string('payment_reference', 128)->nullable()->after('payment_status'); // FR-2
            $table->decimal('payment_amount', 8, 2)->nullable()->after('payment_reference');
            $table->char('payment_currency', 3)->nullable()->default('EUR')->after('payment_amount');
            $table->string('payer_name', 255)->nullable()->after('payment_currency');
            $table->timestamp('payment_claimed_at')->nullable()->after('payer_name');

            // Trazabilidad del dinero: quién dejó pasar esta llamada y cuándo (FR-5)
            $table->timestamp('payment_verified_at')->nullable()->after('payment_claimed_at');
            $table->foreignId('payment_verified_by')->nullable()->after('payment_verified_at')
                ->constrained('users')->nullOnDelete();

            $table->index(['modality', 'status']);
            $table->index('payment_status');
            $table->index(['start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['modality', 'status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['start_time', 'end_time']);

            $table->dropForeign(['payment_verified_by']);

            $table->dropColumn([
                'modality',
                'client_timezone',
                'meeting_url',
                'meeting_provider',
                'google_event_id',
                'google_calendar_id',
                'meeting_link_failed_at',
                'payment_status',
                'payment_reference',
                'payment_amount',
                'payment_currency',
                'payer_name',
                'payment_claimed_at',
                'payment_verified_at',
                'payment_verified_by',
            ]);
        });
    }
};
