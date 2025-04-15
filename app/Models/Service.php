<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'duration',
        'price',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }
    
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    
    /**
     * Mutator to capitalize the service name when set
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }
    
    /**
     * Accessor to ensure the service name is always displayed capitalized
     *
     * @param string $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        return ucwords(strtolower($value));
    }
}
