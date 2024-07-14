<?php

namespace App\Models\Utm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtmSource extends Model
{
    use HasFactory;

    protected $table = 'utm_sources';

    protected $fillable
        = [
            'text',
        ];
}
