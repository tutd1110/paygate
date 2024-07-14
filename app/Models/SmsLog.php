<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $table = 'sms_logs';

    protected $fillable
        = [
            'phone',
            'sms_content',
            'object_id',
            'object_instance',
            'sent_status',
            'data',
            'created_at',
            'updated_at',
            'contact_lead_procees_id',
            'sent_time',
        ];
}
