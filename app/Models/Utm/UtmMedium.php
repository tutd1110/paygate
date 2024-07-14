<?php

namespace App\Models\Utm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtmMedium extends Model
{
    use HasFactory;

    protected $table = 'utm_mediums';

    protected $fillable
        = [
            'text',
        ];
}
