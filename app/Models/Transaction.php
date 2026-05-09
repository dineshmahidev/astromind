<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'payment_id',
        'amount',
        'currency',
        'status',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
