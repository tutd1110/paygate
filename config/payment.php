<?php
return [
    'template_img_qr' => [
        'msb' => '9V2SJJ9',
        'bidv' =>'GZi7SIX',
    ],
    'transfer' => [
        'secretKey' => 'VA-BIDV-HMO',
    ],
    'momo' => [
        'partnerCode' => 'MOMOSVCL20180515',
        'accessKey' => 'c5UlowSzbO2YiFVU',
        'secretKey' => 'qY9GcJY62ov7lEGcRWZk72tTa8ZhTlFz',
        'paygateName' => 'MOMO',
        'partnerId' => 39,
        'paygateUrl' => 'https://payment.momo.vn:18080/gw_payment/transactionProcessor',
    ],
    'momo_v3' => [
        'partnerCode' => 'MOMONWAI20230405',
        'accessKey' => 'G2VMNHaZ7NUGzYwm',
        'secretKey' => 'FYHobwH16R8q6goMzNXa88U3Soh4vh2C',
        'paygateName' => 'MOMO',
        'partnerId' => 39,
        'paygateUrl' => 'https://test-payment.momo.vn/v2/gateway/api/create',
        'requestType'=> 'captureWallet'
    ],
    'payoo' => [

    ],
//    'viettelpay' => [
//        'partnerCode' => 'HOCMAI2',
//        'version' => 'HMBILL_',
//        'payUrl' => 'https://pay3.viettel.vn/PaymentGateway/payment', // https://pay3.viettel.vn
//        'accessCode' => 'd41d8cd98f00b204e9800998ecf8427ee97e470f2b93b4dd347452b0f87cb182',
//        'secretKey' => 'd41d8cd98f00b204e9800998ecf8427ef35258360ee81486900f55d36ab2c7e5'
//    ],
    'viettelpay' => [
        'partnerCode' => 'HOCMAI',
        'version' => 'HMBILL_',
        'payUrl' => 'https://pay3.viettel.vn/PaymentGateway/payment', // https://pay3.viettel.vn
        'accessCode' => 'd41d8cd98f00b204e9800998ecf8427e03a65ab795ae61d29194c227f17d212c',
        'secretKey'=> 'd41d8cd98f00b204e9800998ecf8427edbd914ea6490ff414d98d38066bc094a',
    ],
    'vnpay' => [
        'version'=>'2.1.0',
        'partnerCode' => 'TIDHMO86',
        'command' => 'pay',
        'payUrl' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html', // https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
        'secretKey' => 'AYOEIHRSFFAXKWCVXNLUVHMAMBZFYFIC',
        'ipWhitelist'=> [
            '113.160.92.202', // Sandbox
            '113.52.45.78',
            '116.97.245.130',
            '42.118.107.252',
            '113.20.97.250',
            '203.171.19.146',
            '103.220.87.4',
            '103.220.86.4',
            '171.244.46.46',
            '171.244.46.45',
        ]
    ],
    'zalopay' => [
        'paygateUrl' => 'https://sb-openapi.zalopay.vn',
        'app_id' => 2554,
        'app_user' => 'HOCMAIVN',
        'key1' => 'sdngKKJmqEMzvh5QQcdD2A9XBSKUNaYn',
        'key2' => 'trMrHtvjo6myautxDUiAcYsVtaeQ8nhf',
        'ZALOPAY_EXPIRY_TIME' => 900,
        'redirect_url' => "",
    ],
    'vnptpay' => [

    ],
    'shopeepay' => [

    ],
    'msb' => [
        'va_code' => 'HMO',//Mã đầu số MSB cung cấp
        'username' => env('MSB_USERNAME',''),
        'password' => env('MSB_PASSWORD',''),
        'public_key' => '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnq6SyhaTj4eqMIdCfwG0
jS5Mz+LOak2XfdUyVZ5tAhRiQ9hOrjkwxTiq0NIyaDersyzk4fnyGQHXDbh/zPHM
CneCX0pnkTJHwMmfDLWGDJicuGtsngegk8dlACJ3bSfc8n+cJIJg9wD1PM+HEPwL
iq57u8j4/HTTUV68OeSG0WV2P0D6hMC95G765oMDcMaW6eyg3PtAaNvu87KFCGYv
ekd2wMwrmIyWcws5d4i6fmQRue+Ud7FUISbIG3SjbrW62xx6s+P+/RsVq3aMnLZP
VdGz2dku6wbSV136ldD+BhgHzQFHqQaBj7c3unHThtuvZSPTfeROb22b1F4tkenQ
cQIDAQAB
-----END PUBLIC KEY-----',
        'url' => env('MSB_URL', 'https://externalgw.msb.com.vn'),
    ],

    'ENABLE_SEND_SMS' => env('ENABLE_SEND_SMS', false),
    'onepay' => [
        'vpc_Version' => 2,
        'payUrl' => 'https://mtf.onepay.vn/paygate/vpcpay.op',
        'MerchantID' => 'TESTTRAGOP',
        'Accesscode' => 'D51C5CD6',
        'Hashcode' => 'EB1B7F75EBB2FAABD6763FC37A3628AF',
        'vpc_Locale' => 'vn',
        'url_queryDR' => 'https://mtf.onepay.vn/msp/api/v1/vpc/invoices/queries',
        'vpc_User' => 'op01',
        'vpc_Password' => 'op123456',
        'merchantId_onepay' => 27
    ],
];
