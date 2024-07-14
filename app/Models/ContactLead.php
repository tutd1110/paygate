<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/****
 * Class ContactLead
 *
 * @property string id
 * @property string user_id
 * @property string landing_page_id
 * @property string campaign_id
 * @property string crm_id
 * @property string sashi_id
 * @property string olm_id
 * @property string full_name
 * @property string phone
 * @property string address
 * @property string email
 * @property string class
 * @property string action
 * @property string action_status
 * @property string crm_type
 * @property string description
 * @property string scan
 * @property string utm_medium
 * @property string utm_source
 * @property string utm_campaign
 * @property string is_duplicate
 * @property string is_email_duplicate
 * @property string is_phone_duplicate
 * @property string is_active
 * @property string register_ip
 * @property string created_by
 * @property string updated_by
 * @property array  LandingPage landingPage
 *
 *
 * @property magic  landingPages
 *
 *
 * @package App\Models
 *
 */
class ContactLead extends Model
{
    use HasFactory;

    protected $table = 'contact_leads';

    protected $fillable
        = [
            'id',
            'user_id',
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
            'action_status',
            'crm_type',
            'description',
            'scan',
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
