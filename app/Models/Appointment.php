<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];
    
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
    
    // Estados posibles para las citas
    const STATUS_NEW = 'New';
    const STATUS_PENDING = 'Pending';
    const STATUS_CONFIRMED = 'Confirmed';
    const STATUS_CANCELLED = 'Cancelled';
    const STATUS_COMPLETED = 'Completed';
    
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
}
