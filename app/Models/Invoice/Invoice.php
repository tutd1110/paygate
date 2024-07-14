<?php

namespace App\Models\Invoice;

use App\Models\ContactLeadProcess;
use App\Models\LandingPage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;


    protected $table = 'invoices';

    protected $fillable
        = [
            'code',
            'merchant_code',
            'hm_order_id',
            'hm_cart_id',
            'landing_page_id',
            'user_id',
            'contact_lead_process_id',
            'amount',
            'discount',
            'voucher_code',
            'active_code',
            'is_active_code_used',
            'active_code_used_at',
            'line',
            /***
             * Đã gửi tin nhắn cho khách hàng chưa thanh toán hay chưa
             */
            'is_send_sms_unpaid',
            'is_send_sms_paid',
            'is_crm_pushed',
            'quantity',
            'status',
            'return_url_true',
            'return_url_false',
            'return_data',
            'is_must_push_contact_unpaid',
            'must_push_contact_unpaid_after_time',
            'pushed_contact_unpaid_at',
            'is_must_send_sms_unpaid',
            'must_send_sms_unpaid_after_time',
            'sent_sms_unpaid_at',
            'created_by',
            'updated_by',
        ];

    protected $appends
        = [
            'transfer_syntax',
        ];

    protected $hidden
        = [
//            'code',
        ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function contact()
    {
        return $this->belongsTo(ContactLeadProcess::class, 'contact_lead_process_id');
    }

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }

    public function getTransferSyntaxAttribute()
    {
        if ($this->landingPage && $this->landingPage->transfer_syntax) {
            $phone = $this->contact->phone ?? '';
            $invoiceId = $this->id ?? '';

            $syntax = $this->landingPage->transfer_syntax ?? '';
            $syntax = str_replace('{SDT}', $phone, $syntax);
            $syntax = str_replace('{INVOICE_ID}', $invoiceId, $syntax);


            return $syntax;

        } else {
            return null;
        }
    }

    protected $casts
        = [
            'amount' => 'double',
            'discount' => 'double',
        ];
}
