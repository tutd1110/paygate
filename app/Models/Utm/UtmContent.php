<?php

namespace App\Models\Utm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtmContent extends Model
{
    use HasFactory;

    protected $table = 'utm_contents';

    protected $fillable
        = [
            'text',
        ];
}
