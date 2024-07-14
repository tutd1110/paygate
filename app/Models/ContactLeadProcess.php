<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactLeadProcess extends Model
{
    const VERIFIY_ZNS = 'ZNS';
    const VERIFIY_SMS = 'SMS';
    const VERIFIY_AUTO = 'AUTO';
    const VERIFIY_EMAIL = 'EMAIL';

    use HasFactory;
    use Compoships;

    protected $table = 'contact_lead_process';

    protected $fillable
        = [
            'user_id',
            'contact_lead_id',
            'is_first_contact',
            'landing_page_id',
            /***
             * Trường hợp đối với contact đặt chỗ thì bằng 0 nghĩa là contact này không đến từ contact đặt chỗ
             */
            'is_has_from_reserve_form',
            'campaign_id',
            'crm_id',
            'sashi_id',
            'olm_id',
            'full_name',
            'phone',
            'address',
            'email',
            'class',
            'action',
            /***
             * map với phần đặt chỗ bên phía contact đặt chỗ cũ của hocmai
             */
            'action_status',
            'crm_type',
            'description',
            'utm_medium',
            'utm_source',
            'utm_campaign_id',
            'utm_content_id',
            'utm_creator_id',
            'utm_medium_id',
            'utm_source_id',
            'utm_term_id',
            'utm_campaign',
            'is_duplicate',
            'is_email_duplicate',
            'is_phone_duplicate',
            'is_active',
            'register_ip',
            'gender',
            'birth_day',
            'verified',
            'created_by',
            'updated_by',
        ];

    /***
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }
}
