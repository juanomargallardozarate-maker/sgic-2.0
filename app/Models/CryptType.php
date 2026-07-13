<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'default_capacity',
        'max_capacity',
    ];

    protected $casts = [
        'default_capacity' => 'integer',
        'max_capacity' => 'integer',
    ];
}