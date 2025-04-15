<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyData extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'company_data';

    protected $fillable = [
        'uuid',
        'name',
        'company_name',
        'signature_path',
        'email',
        'phone',
        'address',
        'website',
        'social_media_facebook',
        'social_media_instagram',
        'social_media_twitter',
        'address_google_map',
        'user_id',
        'latitude',
        'longitude'
    ];

    /**
     * Get the user that owns the company data.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
