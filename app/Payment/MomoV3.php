<?php

namespace App\Payment;

use App\Helper\Request;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPaymentRequestMerchant;
use App\Payment\Handle\MomoHandle;

class MomoV3 extends PayGate
{
    protected $handle = "App\Payment\Handle\MomoHandle";

    /**
     * Return pay url
     */
    function getPayUrl(): string
    {
        try {
            $domain = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
            $returnUrl = $domain . '/payment/pgw/' . $this->merchant->code . '/result';
            $ipnUrl = env('PGW_API', '') . '/api/v1/pgw/' . $this->merchant->code . '/paybill';
            $this->createRequest();
            $this->createRequestMerchant();

            $params = array(
                'partnerCode' => $this->config->partnerCode ?? config('payment.momo_v3.partnerCode'),
                'accessKey' => $this->config->accessKey ?? config('payment.momo_v3.accessKey'),
                'requestId' => strval($this->getTransactionCode()),
                'amount' => strval($this->getAmount()),
                'orderId' => strval($this->getTransactionCode()),
                'orderInfo' => 'Hocmai thanh toan hoc phi',
                'redirectUrl' => $returnUrl,
                'ipnUrl' => $ipnUrl,
                'extraData' => $this->order->bill_code,
                'requestType' => $this->config->requestType ?? config('payment.momo_v3.requestType'),
            );
            ksort($params);
            $checksum = $this->createChecksum($params);
            $params['signature'] = $checksum;
            $params['lang'] = 'vi';
            $result = $this->doRequest($this->config->paygateUrl ?? config('payment.momo_v3.paygateUrl'), $params);
            $payUrl = "";
            if (isset($result['resultCode'])) {
                if ($result['resultCode'] == 0) {
                    $payUrl = $result['payUrl'];
                }
            }

            $this->updateRequestMerchant($params);

            return $payUrl;
        } catch (\Exception $e) {
            return "";
        }
    }

    /**
     * Verify bill
     * @throws MomoHandle
     */
    public function getBill()
    {
        // TODO
    }

    /**
     * @throws MomoHandle
     */
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
                || $this->order->status == PgwOrder::STATUS_CANCEL
                || $this->order->status == PgwOrder::STATUS_EXPIRED

            ) {
                throw new $this->handle("Đơn hàng đã được cập nhật");
            }

            if ($this->getAmount() != intval($this->payGateData['amount'])) {
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
            "RspCode" => "00",
            "message" => "Received payment result success",
        ];
        $success = false;
        if ($this->payGateData['resultCode'] == '0') {
            $success = true;
        }
        $this->updatePayLog($response, $success);

        $this->paymentSuccess($success);

        return response()->json($response);
    }

    function result()
    {
        if (intval($_GET['resultCode']) == 1006) {
            $this->cancelURL = $this->cancelPaygate($_GET['extraData']);
        }
        $this->successPayment = false;
        if ($this->payGateData['resultCode'] == '0') {
            $this->successPayment = true;
        }
        $this->valid();
        return parent::result();
    }

    private function valid()
    {
        $data = $this->payGateData;
        if (!isset($data['signature'])) {
            throw new $this->handle('Chữ ký không xác định');
        }

        $validData = [
            'partnerCode',
            'accessKey' ,
            'requestId',
            'amount',
            'orderId',
            'orderInfo',
            'orderType',
            'transId',
            'message',
            'responseTime',
            'resultCode',
            'payType',
            'extraData',
        ];
        $hashData = $this->validHashData($validData, $data);
        $hashData['accessKey'] = $this->config->accessKey;
        ksort($hashData);
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
        $hash = [];
        foreach ($params as $key => $value) {
            $hash[] = $key . '=' . $value;
        }
        return hash_hmac('sha256', implode('&', $hash), $this->config->secretKey ?? config('payment.momo_v3.secretKey'));
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
        if (isset($this->payGateData['resultCode'])) {
            return $this->payGateData['resultCode'];
        }

        return "0";
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
