<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'years',
        'is_temporary',
        'requires_renewal',
        'description',
    ];

    protected $casts = [
        'years' => 'integer',
        'is_temporary' => 'boolean',
        'requires_renewal' => 'boolean',
    ];
}