<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class clientes extends Model
{
    protected $fillable = [
        'name',
        'last_name',
        'cell_phone',
        'DNI',
        'time',
        'date',
    ];
}
