<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'service_id',
        'brand_id',
        'client_first_name',
        'client_last_name',
        'client_email',
        'client_phone',
        'notes',
        'start_time',
        'end_time',
        'status',
        'issue_description',
        'equipment_photo_path',
        'address',
        'modality',
        'client_timezone',
        'meeting_url',
        'meeting_provider',
        'google_event_id',
        'google_calendar_id',
        'meeting_link_failed_at',
        'imminent_reminder_sent_at',
        'payment_status',
        'payment_reference',
        'payment_amount',
        'payment_currency',
        'payer_name',
        'payment_claimed_at',
        'payment_verified_at',
        'payment_verified_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'meeting_link_failed_at' => 'datetime',
        'imminent_reminder_sent_at' => 'datetime',
        'payment_claimed_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'payment_amount' => 'decimal:2',
    ];

    protected $appends = ['equipment_photo_url'];

    // Estados posibles para las citas
    const STATUS_NEW = 'New';

    const STATUS_PENDING = 'Pending';

    const STATUS_CONFIRMED = 'Confirmed';

    const STATUS_CANCELLED = 'Cancelled';

    const STATUS_COMPLETED = 'Completed';

    // Modalidad: presencial (el técnico se desplaza) o remota (videollamada)
    const MODALITY_ONSITE = 'onsite';

    const MODALITY_REMOTE = 'remote';

    // Estado de la declaración de pago. La verificación es manual (research #2):
    // el QR de SumUp no nos avisa de nada, así que 'claimed' es lo que dice el
    // cliente y 'verified' es lo que Cesar ha cotejado en la app de SumUp.
    const PAYMENT_UNPAID = 'unpaid';

    const PAYMENT_CLAIMED = 'claimed';

    const PAYMENT_VERIFIED = 'verified';

    const PAYMENT_REJECTED = 'rejected';

    const PAYMENT_REFUND_PENDING = 'refund_pending';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the brand associated with the appointment.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // Scopes útiles para filtrar citas
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('end_time', '>=', now())
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function scopeRemote($query)
    {
        return $query->where('modality', self::MODALITY_REMOTE);
    }

    public function scopeOnsite($query)
    {
        return $query->where('modality', self::MODALITY_ONSITE);
    }

    /**
     * Bandeja de Cesar: citas remotas cuyo pago dice el cliente que hizo, pero
     * que nadie ha cotejado todavía contra SumUp.
     */
    public function scopePendingPaymentVerification($query)
    {
        return $query->where('modality', self::MODALITY_REMOTE)
            ->where('payment_status', self::PAYMENT_CLAIMED);
    }

    /**
     * Citas confirmadas que se quedaron sin enlace porque el provider automático
     * falló. Cesar tiene que pegarlo a mano (FR-15).
     */
    public function scopeAwaitingManualLink($query)
    {
        return $query->where('modality', self::MODALITY_REMOTE)
            ->whereNotNull('meeting_link_failed_at')
            ->whereNull('meeting_url');
    }

    public function isRemote(): bool
    {
        return $this->modality === self::MODALITY_REMOTE;
    }

    public function isPaymentVerified(): bool
    {
        return $this->payment_status === self::PAYMENT_VERIFIED;
    }

    /**
     * Quién verificó el pago (FR-5). Null si aún no lo ha verificado nadie.
     */
    public function paymentVerifier()
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    public function paymentEvents()
    {
        return $this->hasMany(AppointmentPaymentEvent::class)->orderByDesc('created_at');
    }

    /**
     * Get the public URL for the equipment photo (local public disk or Supabase).
     */
    public function getEquipmentPhotoUrlAttribute()
    {
        if (! $this->equipment_photo_path) {
            return null;
        }

        try {
            if (Storage::disk('public')->exists($this->equipment_photo_path)) {
                return Storage::disk('public')->url($this->equipment_photo_path);
            }

            return Storage::disk('supabase')->url($this->equipment_photo_path);
        } catch (\Exception $e) {
            Log::error('Error getting equipment photo URL', [
                'appointment_id' => $this->id,
                'photo_path' => $this->equipment_photo_path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
