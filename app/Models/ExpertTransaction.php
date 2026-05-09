<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertTransaction extends Model
{
    protected $fillable = ['astrologer_id', 'amount', 'type', 'description', 'status'];

    public function astrologer()
    {
        return $this->belongsTo(Astrologer::class);
    }
}
