<?php

namespace App\Models\Utm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtmTerm extends Model
{
    use HasFactory;

    protected $table = 'utm_terms';

    protected $fillable
        = [
            'text',
        ];
}
