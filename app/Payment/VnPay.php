<?php

namespace App\Payment;

use App\Helper\Request;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPaymentRequestMerchant;
use App\Payment\Handle\VnpayHandle;
use Carbon\Carbon;

class VnPay extends PayGate
{
    protected $handle = "App\Payment\Handle\VnpayHandle";
    function getPayUrl(): string
    {
        try {
            $domain = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
            $returnUrl = $domain . '/payment/pgw/' . $this->merchant->code . '/result';
            $this->createRequest();
            $this->createRequestMerchant();
            $params = array(
                'vnp_Version' => $this->config->version ?? config('payment.vnpay.version'),
                'vnp_Command' => $this->config->command ?? config('payment.vnpay.command'),
                'vnp_TmnCode' => $this->config->partnerCode ?? config('payment.vnpay.partnerCode'),
                'vnp_Amount' => $this->getAmountVnpay(),
                'vnp_CreateDate' => intval($this->getCreateDate(0)),
                'vnp_CurrCode' => 'VND',
                'vnp_OrderType' => 'other',
//                'vnp_BankCode' => 'INTCARD',
                'vnp_IpAddr' => $_SERVER['REMOTE_ADDR'],
                'vnp_Locale' => 'vn',
                'vnp_OrderInfo' => $this->order->bill_code,
                'vnp_ReturnUrl' => $returnUrl,
                'vnp_TxnRef' => strval($this->getTransactionCode()),
                'vnp_ExpireDate' => intval($this->getCreateDate(15)),
            );
            $params['vnp_SecureHash'] = $this->createChecksum($params);
            $payUrl = ($this->config->payUrl ?? config('payment.vnpay.payUrl')) . '?' . http_build_query($params, '', '&');
            return $payUrl;
        } catch (\Exception $e) {
            return $e->getLine() . $e->getMessage();
        }
    }


    function payBill()
    {
        $ip = $this->getIPAddress();
        if (!in_array($ip, config('payment.vnpay.ipWhitelist'))) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message'=> 'IP is not allowed',
                    'ip' => $ip
                ],

            ]);
        }
        try {
            $this->createPayLog("confirm");
            if (intval($this->payGateData['vnp_ResponseCode']) == 99) {
                throw new $this->handle("00","Confirm Success");
            }
            $this->valid();

            if ($this->getAmountVnpay() != intval($this->payGateData['vnp_Amount'])) {
                throw new $this->handle("04","Invalid amount");
            }
            if ($this->requestMerchant->paid_status == PgwPaymentRequestMerchant::PAID_STATUS_SUCCESS
                || $this->request->paid_status == PgwPaymentRequest::PAID_STATUS_SUCCESS
                || $this->order->status == PgwOrder::STATUS_PAID
                || $this->order->status == PgwOrder::STATUS_FAIL
                || $this->order->status == PgwOrder::STATUS_REFUND
                || $this->order->status == PgwOrder::STATUS_CANCEL
                || $this->order->status == PgwOrder::STATUS_EXPIRED
            ) {
                throw new $this->handle("02","Order already confirmed");
            }

        } catch (VnpayHandle $e) {
            $this->updatePayLog($e->getResponse());
            throw $e;
        } catch (\Exception $e) {
            $handle = new $this->handle($e->getMessage());
            $this->updatePayLog($handle->getResponse());
            throw $handle;
        }
        $response = [
            "Message" => "Confirm Success",
            "RspCode" => "00",
        ];
        if ($this->payGateData['vnp_ResponseCode'] == 00) {
            $success = true;
        } else {
            $success = false;
        }
        $this->updatePayLog($response, $success);
        $this->paymentSuccess($success);

        return response()->json($response);
    }

    function result()
    {
        $this->valid();
        if (intval($_GET['vnp_ResponseCode']) == 24) {
            $this->cancelURL = $this->cancelPaygate($_GET['vnp_OrderInfo']);
        }
        $this->successPayment = false;
        if ($this->payGateData['vnp_ResponseCode'] == 00) {
            $this->successPayment = true;
        }
        return parent::result();
    }

    private function valid()
    {
        $data = $this->payGateData;
        if (!isset($data['vnp_SecureHash'])) {
            throw new $this->handle("97","Invalid Checksum");
        }

        $validData = [
            "vnp_Amount",
            "vnp_BankCode",
            "vnp_BankTranNo",
            "vnp_CardType",
            "vnp_OrderInfo",
            "vnp_PayDate",
            "vnp_ResponseCode",
            "vnp_TmnCode",
            "vnp_TransactionNo",
            "vnp_TransactionStatus",
            "vnp_TxnRef",
            "vnp_SecureHash",
        ];
        $hashData = $this->validHashData($validData, $data);
//        if (count($validData) != count($hashData)) {
//            throw new $this->handle("Thiếu dữ liệu đầu vào");
//        }

        if (!$this->validChecksum($hashData, $data['vnp_SecureHash'])) {
            throw new $this->handle("97","Invalid Checksum");
        }
        $this->initOrder($data['vnp_TxnRef']);
        if (!$this->order || !$this->request || !$this->requestMerchant) {
            throw new $this->handle("01","Order Not Found");
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
        $vnp_SecureHash = $params['vnp_SecureHash'] ?? '';
        if (empty($vnp_SecureHash)) {
            ksort($params);
            $hashData = http_build_query($params, '', '&');
            $check_sum = hash_hmac('sha512', $hashData, ($this->config->secretKey ?? config('payment.vnpay.secretKey')));
        } else {
            foreach ($_GET as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }
            unset($params['vnp_SecureHash']);
            ksort($params);
            $hashData = http_build_query($params, '', '&');
            $check_sum = hash_hmac('sha512', $hashData, ($this->config->secretKey ?? config('payment.vnpay.secretKey')));
            if ($check_sum == $vnp_SecureHash) {
                if ($this->payGateData['vnp_ResponseCode'] == '00' || $this->payGateData['vnp_ResponseCode'] == '24') {
                    return $check_sum;
                } else {
                    return '';
                }
            }
        }
        return $check_sum;
    }

    function getCreateDate($returnTime)
    {
        $date = Carbon::now()->addMinutes($returnTime)->format('YmdHis');
        return $date;
    }

    function payGateVpcMerchTxnRef(): string
    {
        if (isset($this->payGateData['vnp_TxnRef'])) {
            return $this->payGateData['vnp_TxnRef'];
        }

        return "";
    }

    function getMerchantInvoice(): string
    {
        if (isset($this->payGateData['vnp_TxnRef'])) {
            return $this->payGateData['vnp_TxnRef'];
        }

        return "";
    }

    function payGateResponseCode()
    {
        if (isset($this->payGateData['errorCode'])) {
            return $this->payGateData['errorCode'];
        }

        return "0";
    }


    function getAmountVnpay(): int
    {
        if ($this->order) {
            return intval($this->order->amount * 100);
        }

        return 1000;
    }
    function getIPAddress() {

        if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}

