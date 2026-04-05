<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_months',
        'active',
        'modules',
        'features',
        'max_users',
    ];

    protected $casts = [
        'active' => 'boolean',
        'modules' => 'array',
        'features' => 'array',
    ];
}
