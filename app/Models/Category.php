<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
     protected $fillable = [
        'category_name', 'description','image','user_id'
    ];

    public function user()
{
return $this->belongsTo(User::class);
}

 public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
