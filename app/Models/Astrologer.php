<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Astrologer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'specialization', 'experience', 'languages', 'bio', 'price_per_minute', 'rating', 'profile_image', 'is_online', 'city'
    ];
}
