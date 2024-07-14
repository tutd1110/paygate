<?php

namespace App\Payment;

use App\Helper\Request;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPaymentRequestMerchant;

class ViettelPay extends PayGate
{
//    protected $handle = "App\Payment\Handle\ViettelPayHandle";

    function getPayUrl(): string
    {

        try {
            $domain = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
            $returnUrl = $domain . '/payment/pgw/' . $this->merchant->code . '/result';
            $ipnUrl = $domain . '/api/v1/pgw/' . $this->merchant->code . '/paybill';
            $cancelUrl = $domain . '/payment/pgw?bill='.$this->order->bill_code;

            $this->createRequest();
            $this->createRequestMerchant();

            $params = array(
                'version' => '2.0',
                'command' => 'PAYMENT',
                'merchant_code' => config('payment.viettelpay.partnerCode'),
                'order_id' => strval($this->getTransactionCode()),
                'trans_amount' => strval($this->getAmount()),
                'locale' => 'vi',
                'desc' => 'Thanh toán trên app Viettel',
                'billcode' => strval($this->getTransactionCode()),
                'login_msisdn' => '',
                'return_url' => $returnUrl,
                'cancel_url'=> $cancelUrl
            );
            $params['check_sum'] = $this->createChecksum($params);

            $payUrl = config('payment.viettelpay.payUrl') . '?' . http_build_query($params, '', '&');
            return $payUrl;
        } catch (\Exception $e) {
            return '';
        }
    }
    function payBill()
    {
        try {
            $this->createPayLog("confirm");

            $this->valid();

            if ($this->requestMerchant->paid_status == PgwPaymentRequestMerchant::PAID_STATUS_SUCCESS
                || $this->request->paid_status == PgwPaymentRequest::PAID_STATUS_SUCCESS
                || $this->order->status == PgwOrder::STATUS_PAID
                || $this->order->status == PgwOrder::STATUS_FAIL
                || $this->order->status == PgwOrder::STATUS_REFUND
            ) {
                throw new $this->handle("Đơn hàng đã được cập nhật");
            }

            if ($this->getAmount() != intval($this->payGateData['trans_amount'])) {
                throw new $this->handle("Số tiền thanh toán không khớp giá trị đơn hàng");
            }
        } catch (MomoHandle $e) {
            $this->updatePayLog($e->getResponse());
            throw $e;
        } catch (\Exception $e) {
            $handle = new $this->handle($e->getMessage());
            $this->updatePayLog($handle->getResponse());
            throw $handle;
        }

        $response = [
            "message" => "Received payment result success",
        ];
        $success = false;
        if ($this->payGateData['errorCode'] == '0') {
            $success = true;
        }
        $this->updatePayLog($response, $success);

        $this->paymentSuccess($success);

        return response()->json($response);
    }

    function result()
    {
        $this->valid();
        return parent::result();
    }

    private function valid()
    {
        $data = $this->payGateData;
        if (!isset($data['check_sum'])) {
            throw new $this->handle('Chữ ký không xác định');
        }

        $validData = [
            'merchant_code',
            'order_id',
            'error_code',
            'payment_status',
            'version',
            'check_sum',
        ];

        $hashData = $this->validHashData($validData, $data);
        if (count($validData) != count($hashData)) {
            throw new $this->handle("Thiếu dữ liệu đầu vào");
        }

        if (!$this->validChecksum($hashData, $data['signature'])) {
            throw new $this->handle("Sai chữ ký");
        }

        $this->initOrder($data['requestId']);
        if (!$this->order || !$this->request || !$this->requestMerchant) {
            throw new $this->handle("Đơn hàng không tồn tại");
        }
    }

    function validHashData($validData, $data): array
    {
        $hash = [];
        foreach ($validData as $value) {
            if (isset($data[$value])) {
                $hash[$value] = trim($data[$value]);
            }
        }
        return $hash;
    }

    function createChecksum($params): string
    {
        $hashData = config('payment.viettelpay.accessCode') .
            $params['billcode'] .
            'PAYMENT' .
            config('payment.viettelpay.partnerCode') .
            $params['order_id'] .
            $params['trans_amount'] .
            $params['version'];
        $check_sum = base64_encode(hash_hmac('sha1', $hashData, config('payment.viettelpay.secretKey'), true));
        return $check_sum;
    }

    function payGateVpcMerchTxnRef(): string
    {
        if (isset($this->payGateData['requestId'])) {
            return $this->payGateData['requestId'];
        }

        return "";
    }

    function getMerchantInvoice(): string
    {
        if (isset($this->payGateData['transId'])) {
            return $this->payGateData['transId'];
        }

        return "";
    }

    function payGateResponseCode()
    {
        if (isset($this->payGateData['errorCode'])) {
            return $this->payGateData['errorCode'];
        }

        return "";
    }

    public function doRequest($endpoint, $data, $method = 'POST')
    {
        $res = Request::request($endpoint, $method, [
            'json' => $data,
            'verify' => false,
        ]);

        return json_decode($res->getBody(), true);
    }

    function getAmount(): int
    {
        if ($this->order) {
            return intval($this->order->amount);
        }

        return 1000;
    }
}

