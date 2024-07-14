<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPushPartner extends Model
{
    use HasFactory;

    protected $table = 'contact_push_partners';

    protected $fillable
        = [
            'contact_lead_id',
            'api_partner_id',
            'contact_lead_process_id',
            'crm_id',
            'partner_contact_id',
            'partner_contact_string_uuid',
            'landing_page_contact_id',
            'reserve_contact_id',
            'extend_info',
        ];
}
