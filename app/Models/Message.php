<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'consultation_id',
        'sender_id',
        'receiver_id',
        'type',
        'content',
        'is_read',
        'duration'
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
