<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiPartner extends Model
{
    use HasFactory;

    protected $table = 'api_partners';

    protected $fillable
        = [
            'code',
            'description',
            'url',
            'token',
            'map_status',
            'is_active',
            'created_by',
            'updated_by',
        ];

    protected $hidden = [
        'url',
        'token',
        'is_active',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'map_status' => 'array',
    ];
}
