<?php

namespace App\Models\PGW;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class PgwPaymentRequest extends Model
{
    use Compoships;
    protected $table = 'pgw_payment_requests';

    const PAID_STATUS_SUCCESS = 'success';
    const PAID_STATUS_UNSUCCESS = 'unsuccess';
    const BANKING_ID_DEFAULT = 0;

    protected $fillable = [
        'partner_code',
        'order_client_id',
        'merchant_id',
        'banking_id',
        'payment_code',
        'transsion_id',
        'payment_value',
        'total_pay',
        'paid_status',
        'url_return_true',
        'url_return_false',
        'url_return_api',
        'custom',
        'is_msb_va',
        'created_at',
        'updated_at'
    ];
    public function partner()
    {
        return $this->belongsTo(PgwPartner::class, 'partner_code', 'code');
    }
    public function merchant()
    {
        return $this->belongsTo(PgwPaymentMerchants::class, 'merchant_id');
    }
    public function banking()
    {
        return $this->belongsTo(PgwBankingList::class, 'banking_id',);
    }
    public function requestMerchant()
    {
        return $this->hasMany(PgwPaymentRequestMerchant::class,  ['payment_request_id', 'merchant_id','banking_id'], ['id', 'merchant_id','banking_id'])->orderBy('id','desc');
    }
}
