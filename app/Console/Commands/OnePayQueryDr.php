<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use App\Models\PGW\PgwPaymentRequestMerchant;
use Carbon\Carbon;
use App\Models\PGW\PgwPaymentMerchants;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPartnerResgistriMerchant;
use App\Models\RequestLog;

class OnePayQueryDr extends Command
{
    protected $signature = 'onepay:queryDR';

    protected $description = 'Onepay query transaction status';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $timeStart = microtime(true);
        $listTrans = $this->getTransactionOnepay();
        $count = $success = $fail = 0;
        if(!empty($listTrans)) {
            foreach ($listTrans as $item) {
                $count ++;
                $vpc_MerchTxnRef = $item->vpc_MerchTxnRef;
                $getPartnerCode = $this->getPartnerCode($item->payment_request_id);
                $getBusiness = $this->getBusinessByPartner($getPartnerCode, config('payment.onepay.merchantId_onepay'));
                $business = json_decode($getBusiness, true);
                $update = $this->updateTransaction($vpc_MerchTxnRef, $business);
                if($update){
                    $responseUpdate = json_decode($update,1);
                    if(isset($responseUpdate['vpc_TxnResponseCode']) && $responseUpdate['vpc_TxnResponseCode'] === '0') {
                        $success++;
                    }else{
                        $fail ++;
                    }
                }
                $item->query_time = $item->query_time + 1;
                $item->save();
            }
        }

        $timeRun = microtime( true );
        echo "Total transaction: " .$count."\n";
        echo "Total update success: " .$success."\n";
        echo "Total update fail: " .$fail."\n";
        echo "Run time : " . ( number_format( $timeRun - $timeStart, 3 ) ) . "s \n";

    }

    public function updateTransaction($tranId, array $business){
        $result = '';
        $getQueryDr = urldecode($this->queryDR($tranId, $business));
        $dataQuery = explode("&",$getQueryDr);

        if(!empty($dataQuery)){
            $dataResponse = [];
            foreach ($dataQuery as $item){
                $temp = explode("=",$item);
                if($temp) {
                    $dataResponse[$temp[0]] = $temp[1];
                }
            }
            if($dataResponse['vpc_TxnResponseCode'] == 0){
                $url_paybill = env('PGW_API')."/api/v1/pgw/onepay/paybill";
                $result = $this->postCurl($url_paybill,$dataResponse);
            }
        }

        return (string)$result;
    }

    public function getTransactionOnepay(){
        $listTrans = PgwPaymentRequestMerchant::query()
                    ->where('merchant_id',config('payment.onepay.merchantId_onepay'))
                    ->where('transaction_status','Y')
                    ->where('respon_code','!=', '0')
                    ->where('created_at', '<=', Carbon::now()->subMinute(30))
                    ->where(function ($q){
                        $q->whereNull('query_time')
                            ->orWhere('query_time','<', 3);
                    })
                    ->get();
        return $listTrans;
    }

    public function queryDR($transID, array $business){
        $params = [
            'vpc_Version'=>1,
            'vpc_Command'=>'queryDR',
            'vpc_AccessCode'=> $business['Accesscode'],
            'vpc_Merchant'=> $business['MerchantID'],
            'vpc_MerchTxnRef'=> $transID,
            'vpc_User'=> $business['vpc_User'],
            'vpc_Password'=> $business['vpc_Password']
        ];

        $Hashcode = ($business['Hashcode'] ?? config('payment.onepay.Hashcode'));
        $params['vpc_SecureHash'] = $this->createChecksum($params, $Hashcode);
        $url = $business['url_queryDR'];
        $response = $this->postCurl($url,$params);

        return $response;

    }

    public function getPartnerCode($paymentRequestId){
        $partnerCode = '';
        $getPaymentRequest = PgwPaymentRequest::query()
            ->where('id',$paymentRequestId)
            ->first();

        if($getPaymentRequest){
            $partnerCode = $getPaymentRequest->partner_code;
        }
        return $partnerCode;
    }

    public function getBusinessByPartner($partnerCode, $merchantId){
        $partnerBusiness = '';
        $getPartnerRegisterMechant = PgwPartnerResgistriMerchant::query()
            ->where('partner_code',$partnerCode)
            ->where('payment_merchant_id',$merchantId)
            ->first();
        if($getPartnerRegisterMechant){
            $partnerBusiness = ($getPartnerRegisterMechant->business) ? $getPartnerRegisterMechant->business : '';
        }

        return $partnerBusiness;
    }

    public function createChecksum($params, $Hashcode)
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
        $checksum = strtoupper(hash_hmac('SHA256',$hashData,pack('H*', $Hashcode)));

        return $checksum;

    }

    public function postCurl($url, $params = [])
    {
        $requestLog = new RequestLog();
        $requestLog->fill([
            'url' => $url,
            'option' => json_encode($params),
            'method' => 'POST',
        ]);

        try {

            $optionClient = array(
                'timeout' => 120,
                'verify' => false
            );

            $client = new Client($optionClient);
            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            ];
            $response = $client->post($url, [
                'headers' => $headers,
                'form_params' => $params
            ]);

            $requestLog->fill([
                'response' => (string)$response->getBody(),
                'is_success' => 1,
                'status_code' => $response->getStatusCode(),
                'headers' => json_encode($response->getHeaders()),
            ]);
            $requestLog->save();

            $content = $response->getBody();

            return $content;

        } catch (\Exception $e) {
            $requestLog->fill([
                'is_success' => 0,
                'exception_info' => json_encode([
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'message' => $e->getMessage(),
                    'trace' => $e->getTrace(),
                ]),
            ]);
            $requestLog->save();

            return $e->getMessage();
        }
    }


}
