<?php

namespace App\Repositories\PGW;

use App\Helper\Request;
use App\Models\PGW\PgwBanks;
use App\Models\PGW\PgwPaymentRequest;

class PushPaymentRequestRepository implements PushPgwPaymentRequetsInterface
{
    const CODE_BANKING_BIDV = 'BIDV';

    private $pgwPayMentRequest;


    public function __construct()
    {
        $this->pgwPayMentRequest = app()->make(PgwPaymentRequest::class);
    }

    /***
     * @param $order
     *
     * Function gửi request sang url_return_api theo method Post
     */
    public function pushPaymentRequest($paymentRequest = [], $status = false, $mechant = [], $paymentRequestMerchant = [], $bankRegister = [], $bank = [])
    {
        if (!empty($status) && !empty($paymentRequest)) {
            try {
                $order = [
                    'partner_code' => $paymentRequest->partner_code,
                    'order_client_id' => $paymentRequest->order_client_id,
                    'total_pay' => intval($paymentRequest->total_pay),
                ];
                ksort($order);
                $order['signature'] = $this->enCryptData($order);
                $getBanking = PgwBanks::find($paymentRequestMerchant['banking_id']);
                $bankingCode = $getBanking['code'] ?? null;
                $vpc_MerchTxnRef = $paymentRequestMerchant->vpc_MerchTxnRef;
                if (!empty($paymentRequestMerchant['banking_id']) &&  $bankingCode == self::CODE_BANKING_BIDV) {
                    $vpc_MerchTxnRef = trim(($bankRegister['code'] ?? '') . $vpc_MerchTxnRef);
                }
                $order['paid_status'] = $paymentRequest->paid_status ?? null;
                $order['vpc_MerchTxnRef'] = $vpc_MerchTxnRef ?? null;    // Mã payment gateway gửi sang cổng thanh toán
                $order['merchant_code'] = $mechant->code ?? null;
                $order['banking_code'] = $bank->code ?? null;
                $order['transsion_id'] = $paymentRequest->transsion_id ?? null;
                $order['custom'] = (!empty($paymentRequest->custom)) ? json_decode($paymentRequest->custom, true) : null;

                Request::post($paymentRequest->url_return_api, [
                    'form_params' => $order
                ]);
            } catch (\Throwable $exception) {
            throw $exception;
            }
        }
    }

    protected static function enCryptData($order)
    {
        $ciphertextRaw = http_build_query($order, '', '&');
        $signature = hash_hmac('sha512', $ciphertextRaw, env('ORDER_ENCODE_KEY'));
        return $signature;
    }

}
