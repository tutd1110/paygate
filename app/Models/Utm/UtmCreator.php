<?php

namespace App\Models\Utm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtmCreator extends Model
{
    use HasFactory;
    protected $table = 'utm_creators';

    protected $fillable
        = [
            'text',
        ];
}
