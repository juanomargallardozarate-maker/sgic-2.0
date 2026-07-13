<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'color',
        'icon',
        'is_available_for_sale',
        'is_operational',
        'order',
    ];

    protected $casts = [
        'is_available_for_sale' => 'boolean',
        'is_operational' => 'boolean',
        'order' => 'integer',
    ];
}