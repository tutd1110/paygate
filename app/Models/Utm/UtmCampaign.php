<?php

namespace App\Models\Utm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtmCampaign extends Model
{
    use HasFactory;

    protected $table = 'utm_campaigns';

    protected $fillable
        = [
            'text',
        ];
}
