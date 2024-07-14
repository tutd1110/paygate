<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPageTracking extends Model
{
    use HasFactory;
    protected $table = 'landing_page_trackings';

    protected $fillable = [
        'landing_page_id',
        'header_bottom',
        'body',
        'body_bottom',
        'footer',
        'created_by',
        'updated_by'
    ];
}
