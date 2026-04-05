<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'number',
        'number_of_beds',
        'type',
        'price',
        'status',
        'id_cliente',
        'id_hotel'
    ];
}
