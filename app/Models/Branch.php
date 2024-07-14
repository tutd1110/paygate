<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branchs';

    protected $fillable = [
        'code',
        'name',
        'description',
        'address',
        'is_active',
        'created_by',
        'updated_by',
    ];
}
