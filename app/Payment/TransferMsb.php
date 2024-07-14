<?php

namespace App\Payment;

use App\Helper\Request;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPartnerRegistriBanking;
use App\Models\PGW\PgwPaymentMerchants;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPaymentRequestMerchant;
use App\Payment\Handle\TransferMsbHandle;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Helper\RedisHelper;

class TransferMsb extends PayGate
{

    const TIME_OUT = 120;
    const expiryDate = 3;
    const MERCHANT_MSB_ID = 8;
    const BANKING_MSB_ID = 6;
    const STATUS_CANCEL_VA_MSB = 0;

    protected $msbUrl = "http://103.89.122.10:7080";

    protected $msbPath = "/msbgateway/services";

    protected $handle = "App\Payment\Handle\TransferMsbHandle";

    /**
     * Return pay url
     */
    function getPayUrl(): string
    {
        try {
            $this->createRequest();
            $this->createRequestMerchant();

            $result = $this->getTransactionCode();

            $expiryDate = self::expiryDate;
            $userInfo = [
                'accountNumber' => $this->getTransactionCode(),
                'referenceNumber' => $this->getTransactionCode(),
                'name' => $this->customer->full_name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
                'expiryDate' => Carbon::now()->addDays($expiryDate)->isoFormat('DD/MM/YYYY hh:mm:ss'),
                'status' => 1,
                'payType' => 1,
                'maxAmount' => "",
                'minAmount' => "",
                'equalAmount' => $this->getAmount(),
                //'suggestAmount' => $this->getAmount()
            ];
            $this->registerVirtualAccount($userInfo,$this->bankRegister->code ?? 'HMO');

            return $result;
        } catch (\Exception $e) {
            return "";
        }
    }


    /**
     * @throws TransferMsbHandle
     */
    function payBill()
    {
        try {
            $this->createPayLog("confirm");
            $validData = [
                "tranSeq",
                "tranDate",
                "vaNumber",
                "tranAmount",
            ];

            $this->valid($validData);

            if ($this->getAmount() != intval($this->payGateData['tranAmount'])) {
                throw new $this->handle("026", "Số tiền thanh toán không khớp giá trị đơn hàng");
            }
        } catch (TransferMsbHandle $e) {
            $this->updatePayLog($e->getResponse());
            throw $e;
        } catch (\Exception $e) {
            $handle = new $this->handle($e->getMessage());
            $this->updatePayLog($handle->getResponse());
            throw $handle;
        }

        $response = [
            "responseCode" => "0",
            "responseDesc" => "Da ghi nhan thanh cong",
            "moreInfo" => null,
            "RspCode" => "00"
        ];
        $this->updatePayLog($response, true);

        $this->paymentSuccess(true);

        return response()->json($response);

    }

    private function valid($validData)
    {
        $data = $this->payGateData;
        if (!isset($data['signature'])) {
            throw new $this->handle("145", 'Chữ ký không xác định');
        }

        $hashData = $this->validHashData($validData, $data);
        if (count($validData) != count($hashData)) {
            throw new $this->handle("145", "Thiếu dữ liệu đầu vào");
        }


        if (!$this->checkSignature($hashData, $data['signature'])) {
            throw new $this->handle("007", "Sai chữ ký");
        }
        $this->initOrder($data['vaNumber']);
        if (!$this->order || !$this->request || !$this->requestMerchant) {
            throw new $this->handle("025", "Khách hàng không có đơn hàng");
        }

        if (!$this->customer) {
            throw new $this->handle("017", "Khách hàng không tồn tại");
        }

        if ($this->requestMerchant->paid_status == PgwPaymentRequestMerchant::PAID_STATUS_SUCCESS
            || $this->request->paid_status == PgwPaymentRequest::PAID_STATUS_SUCCESS
            || $this->order->status == PgwOrder::STATUS_PAID
            || $this->order->status == PgwOrder::STATUS_FAIL
            || $this->order->status == PgwOrder::STATUS_REFUND
            || $this->order->status == PgwOrder::STATUS_CANCEL
            || $this->order->status == PgwOrder::STATUS_EXPIRED
        ) {
            throw new $this->handle("001", "Hóa đơn đã gạch nợ");
        }
    }

    public function checkSignature($signaturePayLoad, $signature)
    {
        $signaturePayLoad = implode('|', $signaturePayLoad);
        $signatureData = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $signature)[1]))));
        if ($signatureData->sub != $signaturePayLoad) {
            return false;
        }

        return true;
    }

    function payGateVpcMerchTxnRef(): string
    {
        if (isset($this->payGateData['vaNumber'])) {
            return $this->payGateData['vaNumber'];
        }

        return "";
    }

    function getMerchantInvoice(): string
    {
        if (isset($this->payGateData['tranSeq'])) {
            return $this->payGateData['tranSeq'];
        }

        return "";
    }

    function payGateResponseCode()
    {
        return "0";
    }

    public function getQRUrl(): string
    {
        $number = $this->getPayUrl();
        $amount = $this->getAmount();
        $content = $this->order->code;
        if ($this->customer) {
            $content .= " " . $this->customer->phone;
        }
        return "https://img.vietqr.io/image/970426-$number-" . config('payment.template_img_qr.msb') . ".jpg?amount=$amount&addInfo=$content";
    }

    public function authentication()
    {
        if (!empty($this->bankRegister)){
            $username = json_decode($this->bankRegister->business,true)['username'] ?? null;
            $password = json_decode($this->bankRegister->business,true)['password'] ?? null;
        }
        $data = [
            'username' => $username ?? config('payment.msb.username'),
            'password' => $password ?? config('payment.msb.password')
        ];
        $response = $this->doRequest('POST', 'RC267', $data);

        $result = $response['respDomain'];

        return $result;
    }

    public function getToken($vaCode)
    {
        $key = "MSB::Accesstoken-".$vaCode;
        $token = RedisHelper::get($key);
        if (!isset($token) || $token === false) {
            $login = $this->authentication();
            if (isset($login['accessToken'])) {
                $token = $login['accessToken'];
                RedisHelper::set($key, $token, 23 * 60 * 60);//cache 23h

                return $token;
            }
        }

        return $token;
    }

    public function registerVirtualAccount($userInfo = [],$vaCode)
    {
        $this->createPayLog("register");

        $serviceCode = 'RC318';
        $tokenKey = $this->getToken($vaCode);

        $data = [
            'serviceCode' => $this->bankRegister->code ?? config('payment.msb.va_code'),
            'tokenKey' => $tokenKey,
            'rows' => [
                $userInfo
            ]
        ];

        $register = $this->doRequest('POST', $serviceCode, $data);

        $result = $register['respDomain']['rows'];
        $response_log = json_encode($result);
        $this->updatePayLog($response_log, true);

        return $result;
    }

    /** Huỷ VA bên ngân hàng MSB khi cancel order */
    public function cancelVirtualAccount($data = [])
    {
        try {
            $paymentMerchantMsb = PgwPaymentRequestMerchant::where('merchant_id', self::MERCHANT_MSB_ID)
                ->where('banking_id', self::BANKING_MSB_ID)
                ->where('order_client_id', $data['id'])
                ->first();
            $bankingRegister = PgwPartnerRegistriBanking::where('partner_code', $data['partner_code'])
                ->where('banking_list_id', self::BANKING_MSB_ID)
                ->first();
            if (!empty($paymentMerchantMsb)) {
                $userInfo = [
                    'accountNumber' => $paymentMerchantMsb['vpc_MerchTxnRef'] ?? null,
                    'referenceNumber' => $paymentMerchantMsb['vpc_MerchTxnRef'] ?? null,
                    'name' => $data['contact']['full_name'] ?? null,
                    'email' => $data['contact']['email'] ?? null,
                    'phone' => $data['contact']['phone'] ?? null,
                    'status' => self::STATUS_CANCEL_VA_MSB,
                ];
                $serviceCode = 'RC319';
                $vaCode = $bankingRegister->code ?? 'HMO';
                $tokenKey = $this->getToken($vaCode);

                $headers = [
                    'Content-Type' => 'application/json',
                    'partnerId' => 'VIRTUAL_ACC',
                    'srvCode' => $serviceCode
                ];

                $data = [
                    'serviceCode' => $bankingRegister->code ?? config('payment.msb.va_code'),
                    'tokenKey' => $tokenKey,
                    'rows' => [
                        $userInfo
                    ]
                ];

                $url = config('payment.msb.url') . $this->msbPath;
                $cancel = false;

                $response = Request::post($url, [
                    'headers' => $headers,
                    'json' => $data
                ]);
                $result = json_decode($response->getBody(), true);
                if (!empty($result['respDomain']['rows']) && $result['respMessage']['respCode'] == 0) {
                    $cancel = true;
                }
                return $cancel;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function doRequest($method, $serviceCode, $params = [])
    {
        try {
            $headers = [
                'Content-Type' => 'application/json',
                'partnerId' => 'VIRTUAL_ACC',
                'srvCode' => $serviceCode
            ];
            $data = [
                'timeout' => self::TIME_OUT,
                'verify' => false,
                'headers' => $headers,
                'json' => $params
            ];
            $url = config('payment.msb.url') . $this->msbPath;

            $response = Request::post($url,$data);
            $content = (string)$response->getBody();

            $result =  json_decode((string)$response->getBody(), true);
            if ($result['respMessage']['respCode'] != '0') {
                $this->updatePayLog($content);
                throw new $this->handle($result['respMessage']['respCode'], $result['respMessage']['respDesc']);
            }

            return $result;

        } catch (TransferMsbHandle $exc) {
            $this->updatePayLog($exc->getResponse());
            throw $exc;
        } catch (\Exception $exc) {
            $handle = new $this->handle($exc->getMessage());
            $this->updatePayLog($handle->getResponse());
            throw $handle;
        }
    }
}
