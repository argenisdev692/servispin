<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentPaymentEvent extends Model
{
    public const TYPE_CLAIMED = 'claimed';

    public const TYPE_VERIFIED = 'verified';

    public const TYPE_REJECTED = 'rejected';

    public const TYPE_LINK_ADDED = 'link_added';

    public const TYPE_LINK_FAILED = 'link_failed';

    public const TYPE_REFUND_PENDING = 'refund_pending';

    public const TYPE_RELEASED = 'released';

    public const TYPE_CONFIRMATION_RESENT = 'confirmation_resent';

    protected $fillable = [
        'appointment_id',
        'event_type',
        'reference',
        'amount',
        'currency',
        'payer_name',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function label(): string
    {
        return match ($this->event_type) {
            self::TYPE_CLAIMED => 'Pago declarado',
            self::TYPE_VERIFIED => 'Pago verificado',
            self::TYPE_REJECTED => 'Pago rechazado',
            self::TYPE_LINK_ADDED => 'Enlace añadido',
            self::TYPE_LINK_FAILED => 'Fallo al generar enlace',
            self::TYPE_REFUND_PENDING => 'Reembolso pendiente',
            self::TYPE_RELEASED => 'Hueco liberado (sin verificar)',
            self::TYPE_CONFIRMATION_RESENT => 'Confirmación reenviada',
            default => $this->event_type,
        };
    }
}
