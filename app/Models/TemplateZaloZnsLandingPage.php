<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateZaloZnsLandingPage extends Model
{
    use HasFactory;

    protected $table = 'template_zalo_zns_landing_pages';

    protected $fillable = [
        'id',
        'landing_page_id',
        'template_id',
        'template_data',
        'status',
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];

}
