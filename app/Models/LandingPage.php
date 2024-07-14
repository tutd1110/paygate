<?php

namespace App\Models;

use App\Models\SYS\SysUserLandingpage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    use HasFactory;

    protected $table = 'landingpages';

    protected $fillable
        = [
            'id',
            'site_id',
            'code',
            'event',
            'department_id',
            'domain_name',
            'description',
            'server_ip',
            'start_time',
            'end_time',
            'status',
            'purpose',
            'developer',
            'type',
            'start_time_coupon',
            'end_time_coupon',
            'allow_reserve_start_time',
            'register_start_time',
            'register_end_time',
            'olm_id',
            'hotline',
            'api_info',
            'send_sms_invoice_delay',
            'push_crm_invoice_delay',
            'transfer_syntax',
            'created_by',
            'updated_by',
            'is_send_sms_paid',
            'auto_cancel_order',
            'verifiy_type',
        ];

    protected $hidden = [
        'server_ip',
    ];

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'landing_page_campaigns', 'landing_page_id', 'campaign_id');
    }

    public function apiPartners()
    {
        return $this->belongsToMany(ApiPartner::class, 'landingpage_api_partners', 'landingpage_id', 'api_partner_id');
    }
    public function User()
    {
        return $this->belongsToMany(User::class, 'sys_user_landing_page', 'landing_page_id', 'user_id');
    }
    public function delete()
    {
        self::deleting(function ($landing) {
            $landing->campaigns()->sync([]);
        });

        return parent::delete();
    }
}
