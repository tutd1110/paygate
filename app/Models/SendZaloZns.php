<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendZaloZns extends Model
{
    use HasFactory;

    protected $table = 'sent_zalo_zns';

    protected $fillable = [
        'id',
        'landing_page_id',
        'contact_lead_processs_id',
        'headers',
        'to_phone',
        'template_id',
        'template_data',
        'status',
        'response',
        'sent_time',
        'created_at',
        'updated_at',
    ];
}
