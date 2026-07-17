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
        'longitude',
    ];

    /**
     * Get the user that owns the company data.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Email del usuario administrador propietario, usado como copia interna.
     *
     * Se resuelve por la relación user_id en lugar de consultar el rol 'Admin':
     * los roles están sembrados en el guard 'sanctum' y el guard por defecto es
     * 'web', por lo que User::role('Admin') lanzaría RoleDoesNotExist.
     */
    public function adminEmail(): ?string
    {
        return $this->user?->email;
    }
}
