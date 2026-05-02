<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = [
        'user_id',
        'astrologer_id',
        'question',
        'answer',
        'status',
        'amount_paid',
        'is_video_call',
        'is_audio_call',
        'video_call_room',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function astrologer()
    {
        return $this->belongsTo(User::class, 'astrologer_id');
    }
}
