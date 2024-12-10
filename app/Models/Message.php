<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_type',
        'recipient_value',
        'message',
        'status',
        'credits_used'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}