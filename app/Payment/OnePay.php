<?php

namespace App\Payment;

use App\Helper\Request;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPaymentRequestMerchant;
use App\Payment\Handle\OnePayHandle;
use Carbon\Carbon;

class OnePay extends PayGate
{
    protected $handle = "App\Payment\Handle\OnePayHandle";

    function getPayUrl(): string
    {
        try {
            $domain = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
            $returnUrl = $domain . '/payment/pgw/' . $this->merchant->code . '/result';
            $this->createRequest();
            $this->createRequestMerchant();
            $params = array(
                'Title' => 'VPC 3-Party',
                'vpc_Merchant' => $this->config->MerchantID ?? config('payment.onepay.MerchantID'),
                'vpc_AccessCode' => $this->config->Accesscode ?? config('payment.onepay.Accesscode'),
                'vpc_MerchTxnRef' => strval($this->getTransactionCode()),
                'vpc_OrderInfo' => $this->order->code,
                'vpc_Amount' => $this->getAmountOnepay(),
                'vpc_ReturnURL' => $returnUrl,
                'vpc_Version' => $this->config->vpc_Version ?? config('payment.onepay.vpc_Version'),
                'vpc_Command' => 'pay',
                'vpc_Locale' => $this->config->vpc_Locale ?? config('payment.onepay.vpc_Locale'),
                'vpc_Currency' => 'VND',
                'vpc_TicketNo' => $_SERVER['REMOTE_ADDR'],
                'vpc_Customer_Phone' => $this->customer->phone,
                'vpc_Customer_Email' => $this->customer->email,
                'AgainLink' => urlencode($_SERVER['HTTP_REFERER']),
            );

            $params['vpc_SecureHash'] = $this->createChecksum($params);
            $payUrl = ($this->config->payUrl ?? config('payment.onepay.payUrl')) . '?' . http_build_query($params, '', '&');

            return $payUrl;
        } catch (\Exception $e) {
            return $e->getLine() . $e->getMessage();
        }
    }

    function payBill()
    {
        try {
            $this->createPayLog("confirm");
            $this->valid();
            if ($this->getAmountOnepay() != intval($this->payGateData['vpc_OrderAmount'])) {
                throw new $this->handle("","Số tiền thanh toán không khớp giá trị đơn hàng");
            }

        } catch (OnePayHandle $e) {
            $this->updatePayLog($e->getResponse());
            throw $e;
        } catch (\Exception $e) {
            $handle = new $this->handle($e->getMessage());
            $this->updatePayLog($handle->getResponse());
            throw $handle;
        }

        $response = [
            "vpc_TxnResponseCode" => $this->payGateData['vpc_TxnResponseCode'],
            "vpc_Message" => $this->payGateData['vpc_Message']
        ];

        $this->successPayment = false;
        if ($this->payGateData['vpc_TxnResponseCode'] == 0) {
            $success = true;
            $this->successPayment = true;
        } else {
            $success = false;
        }
        $this->updatePayLog($response, $success);

        $this->paymentSuccess($success);

        return $response;
    }

    function result()
    {
        try {
            $this->createPayLog("confirm");
            $this->valid();
            if ($this->getAmountOnepay() != intval($this->payGateData['vpc_OrderAmount'])) {
                throw new $this->handle("","Số tiền thanh toán không khớp giá trị đơn hàng");
            }

        } catch (OnePayHandle $e) {
            $this->updatePayLog($e->getResponse());
            throw $e;
        } catch (\Exception $e) {
            $handle = new $this->handle($e->getMessage());
            $this->updatePayLog($handle->getResponse());
            throw $handle;
        }

        $response = [
            "vpc_TxnResponseCode" => $this->payGateData['vpc_TxnResponseCode'],
            "vpc_Message" => $this->payGateData['vpc_Message']
        ];

        $this->successPayment = false;
        if ($this->payGateData['vpc_TxnResponseCode'] == 0) {
            $success = true;
            $this->successPayment = true;
        } else {
            $success = false;
        }
        $this->updatePayLog($response, $success);

        $this->paymentSuccess($success);

        return parent::result();
    }

    private function valid()
    {
        $data = $this->payGateData;
        if (!isset($data['vpc_SecureHash'])) {
            throw new $this->handle("","Chữ ký không xác định");
        }

        if (!$this->validChecksum($data, $data['vpc_SecureHash'])) {
            throw new $this->handle("","Sai chữ ký");
        }
        $this->initOrder($data['vpc_MerchTxnRef']);
        if (!$this->order || !$this->request || !$this->requestMerchant) {
            throw new $this->handle("","Đơn hàng không tồn tại");
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
        if(isset($params['vpc_SecureHash']))
        {
            unset($params['vpc_SecureHash']);
        }
        ksort($params);
        $hashData = '';
        foreach($params as $key => $value) {
            if (strlen($value) > 0) {
                //Lấy tất cả các tham số có tiền tố vpc_ (và user_ nếu có)
                if ((strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
                    $hashData .= $key . "=" . $value . "&";
                }
            }
        }
        //Xóa dấu & thừa cuối chuỗi dữ liệu
        $hashData = rtrim($hashData, "&");
        $Hashcode = ($this->config->Hashcode ?? config('payment.onepay.Hashcode'));
        $checksum = strtoupper(hash_hmac('SHA256',$hashData,pack('H*',$Hashcode)));

        return $checksum;

    }


    function payGateVpcMerchTxnRef(): string
    {
        if (isset($this->payGateData['vpc_MerchTxnRef'])) {
            return $this->payGateData['vpc_MerchTxnRef'];
        }

        return "";
    }

    function getMerchantInvoice(): string
    {
        if (isset($this->payGateData['vpc_MerchTxnRef'])) {
            return $this->payGateData['vpc_MerchTxnRef'];
        }

        return "";
    }

    function payGateResponseCode()
    {
        if (isset($this->payGateData['vpc_TxnResponseCode'])) {
            return $this->payGateData['vpc_TxnResponseCode'];
        }

        return "";
    }


    function getAmountOnepay(): int
    {
        return intval($this->order->amount * 100);

    }
}

