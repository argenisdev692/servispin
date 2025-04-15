<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class AvailabilityException extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'uuid',
        'date',
        'is_available',
        'reason',
    ];
    
    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }
    
    /**
     * Mutator to capitalize the first letter of the reason
     *
     * @param string $value
     * @return void
     */
    public function setReasonAttribute($value)
    {
        $this->attributes['reason'] = ucfirst(trim($value));
    }
    
    /**
     * Accessor to ensure the reason is always displayed with first letter capitalized
     *
     * @param string $value
     * @return string
     */
    public function getReasonAttribute($value)
    {
        return ucfirst($value);
    }
    
    /**
     * Get a formatted date string for display
     *
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('Y-m-d');
    }
    
    /**
     * Get a localized formatted date string 
     *
     * @param string $locale
     * @return string
     */
    public function getLocalizedDateAttribute($locale = 'es')
    {
        $months = [
            'es' => [
                1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
                5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
                9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
            ]
        ];
        
        if ($locale === 'es') {
            return $this->date->format('j') . ' de ' . 
                   $months['es'][$this->date->format('n')] . ' de ' . 
                   $this->date->format('Y');
        }
        
        return $this->date->format('F j, Y');
    }
    
    /**
     * Get color class based on availability status
     *
     * @return string
     */
    public function getStatusColorAttribute()
    {
        return $this->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    }
    
    /**
     * Get label for the status
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        return $this->is_available ? 'Available' : 'Unavailable';
    }
    
    /**
     * Check if this exception represents a holiday or unavailable day
     *
     * @return boolean
     */
    public function isHoliday()
    {
        return !$this->is_available;
    }
    
    /**
     * Scope a query to only include holidays
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHolidays($query)
    {
        return $query->where('is_available', false);
    }
    
    /**
     * Scope a query to only include overrides (available days)
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverrides($query)
    {
        return $query->where('is_available', true);
    }
}
