<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Astrologer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'specialization', 'category', 'experience', 'languages', 'bio', 'price_per_minute', 'rating', 'profile_image', 'is_online', 'city', 'wallet_balance'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(ExpertTransaction::class);
    }
}
