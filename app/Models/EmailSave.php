<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSave extends Model
{
    use HasFactory;

    protected $table = 'email_saves';

    protected $fillable
        = [
            'id',
            'landing_page_id',
            'contact_lead_process_id',
            'from_email',
            'from_name',
            'to_email',
            'to_name',
            'cc_email',
            'bcc_email',
            'reply_to',
            'subject',
            'content',
            'file_attach',
            'send_time',
            'status',
            'send_error',
            'created_at',
        ];


    protected $casts = [
        'cc_email' => 'array',
        'bcc_email' => 'array',
    ];

    public function contact()
    {
        return $this->belongsTo(ContactLeadProcess::class, 'contact_lead_process_id');
    }

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }
}
