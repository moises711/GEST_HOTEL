<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckinEvent extends Model
{
    protected $fillable = [
        'hotel_id',
        'guest_id',
        'room_id',
        'guest_name',
        'room_number',
        'action',
        'message',
        'happened_at',
    ];

    protected $casts = [
        'happened_at' => 'datetime',
    ];
}
