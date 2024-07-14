<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPageInfo extends Model
{
    use HasFactory;

    protected $table = 'landing_page_info';

    protected $fillable =
        [
            'landing_page_id',
            'transfer_syntax',
            'sms_content_paid',
            'sms_content_remind',
            'email_content_paid',
            'email_content_remind',
            'created_by',
            'updated_by',
        ];
}
