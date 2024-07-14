<?php

namespace App\Models\PGW;

use App\Models\ContactLeadProcess;
use App\Models\ContactLeadProcessReserveLog;
use App\Models\LandingPage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PgwOrder extends Model
{
    use HasFactory;

    protected $table = 'pgw_orders';

    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';
    const STATUS_WAITING = 'waiting';
    const STATUS_PAID = 'paid';
    const STATUS_FAIL = 'fail';
    const STATUS_REFUND = 'refund';
    const STATUS_CANCEL = 'cancel';
    const STATUS_EXPIRED = 'expired';
    const PAID_STATUS_SUCCESS = 'success';
    const PAID_STATUS_UNSUCCESS = 'unsuccess';
    const MERCHANT_TRANSFER = 'transfer';
    const ORDER_CLIENT_STATUS_PAID = '1';
    const ORDER_CLIENT_STATUS_NOT_PAID = '0';

    protected $fillable = [
        'bill_code',
        'code',
        'partner_code',
        'landing_page_id',
        'contact_lead_process_id',
        'order_client_id',
        'payment_request_id',
        'amount',
        'discount',
        'discount_detail',
        'coupon_code',
        'code_reverse',
        'quantity',
        'status',
        'order_status',
        'is_api',
        'merchant_code',
        'banking_code',
        'return_url_true',
        'return_url_false',
        'return_data',
        'url_return_api',
        'custom',
        'description',
        'line',
        'created_by',
        'updated_by',
    ];
    public function orderDetail()
    {
        return $this->hasMany(PgwOrderDetail::class, 'order_id');
    }

    public function contact()
    {
        return $this->belongsTo(ContactLeadProcess::class, 'contact_lead_process_id');
    }
    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }
    public function partner()
    {
        return $this->belongsTo(PgwPartner::class, 'partner_code','code');
    }
    public function paymentMerchant()
    {
        return $this->belongsTo(PgwPaymentMerchants::class, 'merchant_code','code');
    }
    public function banking()
    {
        return $this->belongsTo(PgwBankingList::class, 'banking_code','code');
    }
    public function contactLeadProcessReserveLogs()
    {
        return $this->belongsTo(ContactLeadProcessReserveLog::class, 'contact_lead_process_id','contact_lead_process_id');
    }
    public function paymentRequest()
    {
        return $this->belongsTo(PgwPaymentRequest::class, 'id','order_client_id');
    }
    public function paymentRequestMerchant()
    {
        return $this->hasMany(PgwPaymentRequestMerchant::class, 'order_client_id','id');
    }



    public function checkActiveCodePaid($partner_code, $code){
        if(empty($partner_code) || empty($code)){
            return false;
        }
        $getOrder = PgwOrder::query()->where([
            ['partner_code',$partner_code],
            ['code',$code],
            ['status',self::STATUS_PAID]
        ]);
        return $getOrder->first();
    }

    public function getOrderByBillcode($bill_code){
        if(empty($bill_code)){
            return false;
        }

        $getOrder = PgwOrder::query()->where([
            ['bill_code',$bill_code]
        ]);

        return $getOrder->first();
    }

}
