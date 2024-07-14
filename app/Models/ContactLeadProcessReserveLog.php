<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactLeadProcessReserveLog extends Model
{
    use HasFactory;

    protected $table = 'contact_lead_process_reserve_logs';

    protected $fillable
        = [
            'contact_lead_process_id',
            /***
             * Trường hợp đối với contact đặt chỗ thì bằng 0 nghĩa là contact này không đến từ form contact đặt chỗ
             */
            'is_has_from_reserve_form',
            'coupon_code',
            'phone',
            'send_phone',
            'event',
            'line',
            'status',
            'sms_content',
            'is_crm_pushed',
            'crm_pushed_at',
            'landing_page_id'
        ];

    public function contactLeadProcess()
    {
        return $this->belongsTo(ContactLeadProcess::class, 'contact_lead_process_id');
    }
}
