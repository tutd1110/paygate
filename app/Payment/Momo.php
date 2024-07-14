<?php

namespace App\Payment;

use App\Helper\Request;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPaymentRequestMerchant;
use App\Payment\Handle\MomoHandle;

class Momo extends PayGate
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
                'partnerCode' => $this->config->partnerCode,
                'accessKey' => $this->config->accessKey,
                'requestId' => strval($this->getTransactionCode()),
                'amount' => strval($this->getAmount()),
                'orderId' => strval($this->getTransactionCode()),
                'orderInfo' => 'Hocmai thanh toan hoc phi',
                'returnUrl' => $returnUrl,
                'notifyUrl' => $ipnUrl,
                'extraData' => '',
            );
            $checksum = $this->createChecksum($params);
            if (!empty($this->config->requestType)) {
                $params['requestType'] = $this->config->requestType;
            } else {
                $params['requestType'] = 'captureMoMoWallet';
            }
            $params['signature'] = $checksum;
            $params['lang'] = 'vi';

            $result = $this->doRequest($this->config->paygateUrl, $params);
            $payUrl = "";
            if (isset($result['errorCode'])) {
                if ($result['errorCode'] == 0) {
                    $validData = [
                        'requestId',
                        'orderId',
                        'message',
                        'localMessage',
                        'payUrl',
                        'errorCode',
                        'requestType',
                    ];
                    $hash = $this->validHashData($validData, $result);
                    if (isset($result['signature']) && $this->validChecksum($hash, $result['signature'])) {
                        $payUrl = $result['payUrl'];
                    }
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
        if (!isset($data['signature'])) {
            throw new $this->handle('Chữ ký không xác định');
        }

        $validData = [
            'partnerCode',
            'accessKey',
            'requestId',
            'amount',
            'orderId',
            'orderInfo',
            'orderType',
            'transId',
            'message',
            'localMessage',
            'responseTime',
            'errorCode',
            'payType',
            'extraData',
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
        $hash = [];
        foreach ($params as $key => $value) {
            $hash[] = $key . '=' . $value;
        }
        return hash_hmac('sha256', implode('&', $hash), $this->config->secretKey);
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
