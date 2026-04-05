<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class registros extends Model
{
    protected $fillable = [
        'id_cliente',
        'id_hotel',
        'id_room',
        'check_in',
        'check_out',
    ];
}
