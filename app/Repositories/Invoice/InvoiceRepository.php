<?php

namespace App\Repositories\Invoice;

use App\Helper\SendSms;
use App\Helper\ShortLink;
use App\Models\Invoice\Invoice;
use App\Models\LandingPage;
use App\Models\MessageTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceRepository implements InvoiceInterface
{

    private $invoiceModel;
    private $landingPageModel;

    public function __construct()
    {
        $this->invoiceModel = app()->make(Invoice::class);
        $this->landingPageModel = app()->make(LandingPage::class);
    }

    /***
     * Tạo đơn hàng
     */

    public function create($data)
    {

        $data['code'] = Str::uuid();

        try {

            $invoice = DB::transaction(function () use ($data) {
                $sumMoney = 0;
                if (!isset($data['voucher_code'])) {
                    $data['voucher_code'] = '';
                }
                $data['quantity'] = !empty($data['item_quantity']) ? collect($data['item_quantity'] ?? [])->sum() : 1;
                /***
                 * @var $invoice Invoice
                 */
                $invoice = Invoice::create($data);


                foreach ($data['item_product_id'] as $key => $eachItem) {
                    $newItem = $invoice->items()->create([
                        'product_id' => $eachItem,
                        'product_type' => $data['item_product_type'][$key],
                        'product_name' => $data['item_product_name'][$key] ?? '',
                        'quantity' => $data['item_quantity'][$key] ?? 1,
                        'price' => $data['item_price'][$key],
                        'discount' => $data['item_discount'][$key] ?? 0,
                    ]);

                    $sumMoney += ($newItem->price * $newItem->quantity);

                }
                $invoice->amount = $sumMoney;
                $invoice->items;

                $contactLeadProcess = $invoice->contact;
                $contactLeadProcess->description = 'Người dùng thanh toán đơn hàng: '.$this->getHocMaiPayLink($invoice);

                if ($invoice->landingPage) {
                    /***
                     * Tính thời gian cần push vào Crm nếu landing_page này được cấu hình
                     * Thời gian cần gửi tin nhắn nếu chưa thanh toán
                     *
                     */
                    if ($invoice->landingPage->send_sms_invoice_delay) {
                        $invoice->is_must_send_sms_unpaid = 1;
                        $invoice->must_send_sms_unpaid_after_time = Carbon::now()
                            ->addMinutes($invoice->landingPage->send_sms_invoice_delay);

                    }

                    if ($invoice->landingPage->push_crm_invoice_delay) {
                        $invoice->is_must_push_contact_unpaid = 1;
                        $invoice->must_push_contact_unpaid_after_time = Carbon::now()
                            ->addMinutes($invoice->landingPage->push_crm_invoice_delay);

                    }
                }

                $invoice->save();
                $contactLeadProcess->save();

                $invoice->makeVisible('code');

                return $invoice;
            });

            return $invoice;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function sendSmsUnpaid(Invoice $invoice)
    {

        /***
         * lấy nội dung default đã được cấu hình
         */
        $template = MessageTemplate::where('code', 'invoice_sms_unpaid')->where('landing_page_id', 0)->first();

        $smsContent = $template->content;

        $hotline = '19006933';
        $landingpage = $invoice->landingPage;

        if ($landingpage) {
            if ($landingpage->hotline) {
                $hotline = $landingpage->hotline;
            }

            $template = MessageTemplate::where('code', 'invoice_sms_unpaid')->where('landing_page_id', $landingpage->id)
                ->first();

            if ($template->content ?? '') {
                $smsContent = $template->content;
            }
        }


        /***
         * Tạo link rút gọn
         */
        $targetLink = $this->getHocMaiPayLink($invoice);
        $customUrl = Str::random('3').$invoice->id;
        $link = ShortLink::handler($targetLink, $customUrl);

        $smsContent = str_replace('{link}', $link, $smsContent);
        $smsContent = str_replace('{hotline}', $hotline, $smsContent);
        $sendSms = SendSms::send($invoice->contact->phone, $smsContent, $invoice->id, get_class((object)$invoice));

        if ($sendSms) {
            $invoice->is_send_sms_unpaid = 1;
            $invoice->sent_sms_unpaid_at = Carbon::now();
        } else {
            $invoice->is_send_sms_unpaid = 0;
        }

        $invoice->save();

        return $invoice;

    }

    /***
     * @param Invoice $invoice
     *
     * @return bool
     */
    public function sendSmsPaymentSuccess(Invoice $invoice)
    {
        if ($invoice->is_send_sms_paid == 1) {
            return true;
        }
        /***
         * lấy nội dung default đã được cấu hình
         */
        $template = MessageTemplate::where('code', 'invoice_sms_active_code')->where('landing_page_id', 0)->first();

        $smsContent = $template->content;

        $hotline = '19006933';
        $landingpage = $invoice->landingPage;

        if ($landingpage) {
            if ($landingpage->hotline) {
                $hotline = $landingpage->hotline;
            }

            $template = MessageTemplate::where('code', 'invoice_sms_active_code')->where('landing_page_id', $landingpage->id)
                ->first();

            if ($template->content ?? '') {
                $smsContent = $template->content;
            }
        }

        /***
         * Tạo link rút gọn
         */

        $smsContent = str_replace('{active_code}', $invoice->active_code, $smsContent);
        $smsContent = str_replace('{hotline}', $hotline, $smsContent);
        $sendSms = SendSms::send($invoice->contact->phone, $smsContent, $invoice->id, get_class((object)$invoice));

        if ($sendSms) {
            $invoice->is_send_sms_paid = 1;
        } else {
            $invoice->is_send_sms_paid = 0;
        }

        $invoice->save();

        return (boolean)$invoice->is_send_sms_paid;
    }

    public function getHocMaiPayLink(Invoice $invoice)
    {
        return "https://hocmai.vn/payment/quickpay/?bill={$invoice->code}";
    }

}
