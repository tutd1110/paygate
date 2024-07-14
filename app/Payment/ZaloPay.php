<?php

namespace App\Payment;

use App\Helper\Request;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPaymentRequestMerchant;
use App\Payment\Handle\ZaloPayHandle;
use GuzzleHttp\Client;

class ZaloPay extends PayGate
{
    protected $handle = "App\Payment\Handle\ZaloPayHandle";

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
            $timed = time();

            $app_trans_id_str = $this->getTransactionCode();
            $app_trans_id_str_arr = explode("-",$app_trans_id_str);
            $app_trans_id = $app_trans_id_str_arr[0].$app_trans_id_str_arr[1].$app_trans_id_str_arr[2];
            $params = [
                'app_id'        => $this->config->app_id,
                'app_time'      => $timed * 1000,
                'app_trans_id'  => date("ymdHis") . '_'.strval($app_trans_id),
                'orderId'       => strval($app_trans_id),
                'app_user'      => $this->config->app_user,
                'item'          => '[]',
                'embed_data'    => '{}',
                'amount'        => strval($this->getAmount()),
                'description'   => 'HOCMAI - Thanh toán đơn hàng #'. strval($app_trans_id),
                'bank_code'     => 'zalopayapp',
                'callback_url'  => $ipnUrl
            ];

            $checksum = $this->createChecksum($params);
            $params['mac'] = $checksum;

            $result = $this->doRequest($this->config->paygateUrl."/v2/create", $params);

            $payUrl = "";
            if (isset($result['return_code']) && $result['return_code'] == 1) {
                $payUrl = $result['order_url'];
            }

            $this->updateRequestMerchant($params);

            return $payUrl;
        } catch (\Exception $e) {
            return "";
        }
    }

    /**
     * Verify bill
     * @throws ZaloPayHandle
     */
    public function getBill()
    {
        // TODO
    }

    /**
     * @throws ZaloPayHandle
     */
    function payBill()
    {
        try {
            $this->createPayLog("confirm");

            $this->valid();

            $data = $this->payGateData['data'];

            if ($this->getAmount() != intval($data['amount'])) {
                throw new $this->handle("-14","Số tiền thanh toán không khớp giá trị đơn hàng");
            }
        } catch (ZaloPayHandle $e) {
            $this->updatePayLog($e->getResponse());
            throw $e;
        } catch (\Exception $e) {
            $handle = new $this->handle($e->getMessage());
            $this->updatePayLog($handle->getResponse());
            throw $handle;
        }

        $response = [
            "return_code" => 1,
            "return_message" => "success"
        ];

        $this->updatePayLog($response, true);
        $this->paymentSuccess(true);

        return response()->json($response);
    }

    private function valid()
    {
        $dataPost = $this->payGateData;
        $data = $dataPost['data'];
        $mac        = hash_hmac("sha256", json_encode($data), $this->config->key2);
        $requestmac = $dataPost["mac"];
        if (strcmp($mac, $requestmac) != 0) {
            throw new $this->handle('-1','mac not equal');
        }

        $app_trans_id = $data['app_trans_id'];
        $app_trans_id_temp = explode("_",$app_trans_id);
        $orderId = 0;
        if(isset($app_trans_id_temp[1])){
            $orderId = $app_trans_id_temp[1];
        }

        $orderId_str = substr($orderId,0,3)."-".substr($orderId,3,6)."-".substr($orderId,9);

        $this->initOrder($orderId_str);
        if (!$this->order || !$this->request || !$this->requestMerchant) {
            throw new $this->handle("101","Đơn hàng không tồn tại");
        }

    }


    function createChecksum($params): string
    {
        $str_sha = $params['app_id'] . '|' . $params['app_trans_id'] . '|' . $params['app_user'] . '|'
            . $params['amount'] . '|' . $params['app_time'] . '|' . $params['embed_data'] . '|'
            . $params['item'];
        return hash_hmac('sha256', $str_sha, $this->config->key1);
    }

    function payGateVpcMerchTxnRef(): string
    {
        if (isset($this->payGateData['data'])) {
            $data = $this->payGateData['data'];
            $app_trans_id = $data['app_trans_id'];
            $app_trans_id_temp = explode("_",$app_trans_id);
            $orderId = 0;
            if(isset($app_trans_id_temp[1])){
                $orderId = $app_trans_id_temp[1];
            }

            return substr($orderId,0,3)."-".substr($orderId,3,6)."-".substr($orderId,9);
        }

        return "";
    }

    function getMerchantInvoice(): string
    {
        if (isset($this->payGateData['app_trans_id'])) {
            return $this->payGateData['app_trans_id'];
        }
        return "";
    }

    function payGateResponseCode()
    {
        // TODO
        return "";
    }

    public function doRequest($endpoint, $data, $method = 'POST')
    {
        $client = new Client();
        $res = $client->request($method, $endpoint, [
            'form_params' => $data,
            'verify' => false
        ]);

        return json_decode($res->getBody(), true);
    }
}
