<?php

namespace App\Payment;

use App\Exceptions\InvalidArgumentException;
use App\Exceptions\PaymentException;
use App\Exceptions\ResourceNotFoundException;
use App\Helper\FireBaseHelper;
use App\Helper\RandomHelper;
use App\Helper\RedisHelper;
use App\Helper\Request;
use App\Jobs\PushContact\PushContactToCrm;
use App\Jobs\PushDataFireBase\PushDataFireBase;
use App\Jobs\PushPgwPaymentRequest\PushPgwPaymentRequest;
use App\Jobs\SendEmailHocMai;
use App\Lib\PushContactStatus;
use App\Models\ActiveCode;
use App\Models\ContactLead;
use App\Models\ContactLeadProcess;
use App\Models\EmailSave;
use App\Models\PGW\PgwBankingList;
use App\Models\PGW\PgwBanks;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPartnerRegistriBanking;
use App\Models\PGW\PgwPartnerResgistriMerchant;
use App\Models\PGW\PgwPaymentMerchants;
use App\Models\PGW\PgwPaymentPayLog;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPaymentRequestMerchant;
use App\Helper\SendSms;
use App\Models\MessageTemplate;
use App\Models\ThirdParty;
use App\Payment\Handle\VnpayHandle;
use App\Repositories\Contact\ContactPushEloquentRepository;
use Firebase\JWT\JWT;
use App\Models\ContactLeadProcessReserveLog;

abstract class PayGate
{
    /**
     * The configs connect to pay gate.
     *
     */
    protected $config;

    /**
     * The information of order.
     *
     * @var PgwOrder
     */
    protected $order;

    /**
     * The merchant info.
     *
     * @var PgwPaymentMerchants
     */
    protected $merchant;

    /**
     * Bank
     *
     * @var PgwBankingList
     */
    protected $bank;

    /**
     * Payment request
     *
     * @var PgwPaymentRequest
     */
    protected $request;

    /**
     * Payment request
     *
     * @var PgwPaymentRequestMerchant
     */
    protected $requestMerchant;

    /**
     * Customer
     *
     * @var ContactLeadProcess
     */
    protected $customer;

    /**
     * Payment request
     *
     * @var PaymentException
     */
    protected $handle = "App\Exceptions\PaymentException";

    protected $payGateData;

    /**
     * @var PgwPaymentPayLog
     */
    protected $payLog;

    /**
     * Partner banking register
     *
     * @var PgwPartnerRegistriBanking
     */
    protected $bankRegister;

    public function __construct()
    {
        $this->access_token = env('JWT_ACCESS_KEY');
    }

    /**
     * Init config connect to pay gate
     * @param int $orderId
     * @param int $paymentMerchantId
     * @param int $bankRegId
     * @return PayGate|null
     */
    public static function init(int $orderId, int $merchantRegId, int $bankRegId): ?PayGate
    {
        if (!$merchantRegId) {
            throw new InvalidArgumentException('Cổng thanh toán không xác định');
        }

        if (!$orderId) {
            throw new InvalidArgumentException('Đơn hàng không xác định');
        }

        $merchantRegister = PgwPartnerResgistriMerchant::find($merchantRegId);
        if (!$merchantRegister) {
            throw new ResourceNotFoundException('Cổng thanh toán không chưa được đăng ký');
        }

        $paymentMerchant = PgwPaymentMerchants::find($merchantRegister->payment_merchant_id);
        if ($paymentMerchant->status != PgwPaymentMerchants::STATUS_ACTIVE) {
            throw new InvalidArgumentException('Cổng thanh toán không hoạt động');
        }

        $order = PgwOrder::find($orderId);
        if (!$order) {
            throw new ResourceNotFoundException('Đơn hàng không tồn tại');
        }

        if ($order->status == PgwOrder::STATUS_PAID) {
            throw new InvalidArgumentException('Đơn hàng đã được thanh toán');
        }

        if ($order->status == PgwOrder::STATUS_FAIL) {
            throw new InvalidArgumentException('Đơn hàng thanh toán thất bại. Vui lòng thanh toán lại');
        }

        if ($order->status == PgwOrder::STATUS_REFUND) {
            throw new InvalidArgumentException('Đơn hàng đã được hoàn trả');
        }

        if ($order->status == PgwOrder::STATUS_CANCEL) {
            throw new InvalidArgumentException('Đơn hàng đã được huỷ');
        }

        if ($order->status == PgwOrder::STATUS_EXPIRED) {
            throw new InvalidArgumentException('Đơn hàng đã hết hạn');
        }

        if (intval($order->amount) <= 0) {
            throw new InvalidArgumentException('Giá trị đơn hàng không hợp lệ');
        }

        $customer = ContactLeadProcess::find($order->contact_lead_process_id);
        if (!$customer) {
            throw new InvalidArgumentException('Khách hàng không xác định');
        }

        $bankCode = "";
        $bankRegister = null;
        $bank = null;
        if ($paymentMerchant->type == 'transfer') {
            if (!$bankRegId) {
                throw new InvalidArgumentException('Ngân hàng chuyển khoản không xác định');
            }

            $bankRegister = PgwPartnerRegistriBanking::find($bankRegId);
            if (!$bankRegister) {
                throw new InvalidArgumentException('Ngân hàng chuyển khoản chưa được đăng ký');
            }

            $bank = PgwBankingList::find($bankRegister->banking_list_id);
            if (!$bank) {
                throw new InvalidArgumentException('Ngân hàng chuyển khoản không tồn tại');
            }

            if ($bank->status != PgwBanks::STATUS_ACTIVE) {
                throw new InvalidArgumentException('Ngân hàng chuyển khoản đang tạm đóng');
            }

            $bankCode = $bank->code;
        }

        if($paymentMerchant->code == 'onepay'){
            if(intval($order->amount < 3000000)){
                throw new InvalidArgumentException('Giá trị đơn hàng trả góp từ 3.000.000đ. Bạn vui lòng chọn hình thức thanh toán khác.');
            }
        }

        $payGate = self::getPayGate($paymentMerchant->code, $bankCode);
        if (!$payGate) {
            throw new InvalidArgumentException('Lỗi kết nối tới công thanh toán');
        }

        $payGate->setOrder($order);
        $payGate->setCustomer($customer);
        $payGate->setMerchant($paymentMerchant);
        $payGate->setBank($bank);
        $payGate->setBankRegister($bankRegister);

        if ($bankRegister) {
            if (!$bankRegister->business) {
                throw new InvalidArgumentException('Lỗi kết nối tới công thanh toán');
            }
            $payGate->setConfig(json_decode($bankRegister->business));
        } else {
            if (!$merchantRegister->business) {
                throw new InvalidArgumentException('Lỗi kết nối tới công thanh toán');
            }
            $payGate->setConfig(json_decode($merchantRegister->business));
        }

        return $payGate;
    }

    /**
     * Init config connect to pay gate
     * @param string $paymentMerchantCode
     * @param array $params
     * @return PayGate|null
     */
    public static function initByPMC(string $paymentMerchantCode, array $params): ?PayGate
    {
        if (!$paymentMerchantCode) {
            throw new InvalidArgumentException('Cổng thanh toán không xác định');
        }
        $merchantCode = $paymentMerchantCode;
        $bankCode = "";
        $codeArr = explode('-', $paymentMerchantCode);
        if ($codeArr[0] == 'transfer') {
            $merchantCode = $codeArr[0];
            if (isset($codeArr[1])) {
                $bankCode = $codeArr[1];
            }
        }

        $payGate = self::getPayGate($merchantCode, $bankCode);
        if (!$payGate) {
            throw new InvalidArgumentException('Lỗi kết nối tới công thanh toán');
        }

        $payGate->parseGateData($params);
        $vpcMerchTxnRef = $payGate->payGateVpcMerchTxnRef();
        if (!$vpcMerchTxnRef) {
            throw new InvalidArgumentException('Đơn thanh toán không xác định');
        }

        $requestMerchant = PgwPaymentRequestMerchant::where('vpc_MerchTxnRef', $vpcMerchTxnRef)->first();
        if (!$requestMerchant) {
            if ($paymentMerchantCode == 'vnpay'){
                throw new VnpayHandle("01","Order Not Found");
            }
            throw new InvalidArgumentException('Mã thanh toán không tồn tại');
        }

        $order = PgwOrder::find($requestMerchant->order_client_id);
        if (!$order) {
            if ($paymentMerchantCode == 'vnpay'){
                throw new VnpayHandle("01","Order Not Found");
            }
            throw new ResourceNotFoundException('Đơn hàng không tồn tại');
        }

        $merchantRegister = PgwPartnerResgistriMerchant::where('partner_code', $order->partner_code)->where('payment_merchant_id', $requestMerchant->merchant_id)->first();
        if (!$merchantRegister) {
            throw new ResourceNotFoundException('Cổng thanh toán không chưa được đăng ký');
        }

        $paymentMerchant = PgwPaymentMerchants::find($requestMerchant->merchant_id);
        if (!$paymentMerchant) {
            throw new ResourceNotFoundException('Cổng thanh toán không tồn tại');
        }

        if ($paymentMerchant->status != PgwPaymentMerchants::STATUS_ACTIVE) {
            throw new InvalidArgumentException('Cổng thanh toán không hoạt động');
        }

        if ($paymentMerchant->type == 'transfer') {
            if (!$requestMerchant->banking_id) {
                throw new InvalidArgumentException('Thanh toán chuyển khoản không hợp lệ');
            }

            $bankRegister = PgwPartnerRegistriBanking::where('partner_code', $order->partner_code)->where('banking_list_id', $requestMerchant->banking_id)->first();
            if (!$bankRegister) {
                throw new InvalidArgumentException('Ngân hàng chuyển khoản chưa được đăng ký');
            }

            if (!$bankRegister->business) {
                throw new InvalidArgumentException('Lỗi kết nối tới công thanh toán');
            }

            $payGate->setConfig(json_decode($bankRegister->business));
        } else {
            if (!$merchantRegister->business) {
                throw new InvalidArgumentException('Lỗi kết nối tới công thanh toán');
            }

            $payGate->setConfig(json_decode($merchantRegister->business));
        }
        return $payGate;
    }

    private static function getPayGate($merchantCode, string $bankCode): ?PayGate
    {
        switch ($merchantCode) {
//            case 'momo':
//                return new Momo();
//            momo bản cũ
            case 'momo':
                return new MomoV3();
            case 'payoo':
                return new Payoo();
            case 'shopeepay':
                return new ShopeePay();
            case 'viettelpay':
                return new ViettelPay();
            case 'vnptpay':
                return new VnptPay();
            case 'vnpay':
                return new VnPay();
            case 'zalopay':
                return new ZaloPay();
            case 'transfer':
                if ($bankCode == 'MSB') {
                    return new TransferMsb();
                } else {
                    return new TransferBidv();
                }
            case 'transfermsb':
                return new TransferMsb();
            case 'onepay':
                return new OnePay();
            default:
                return null;
        }
    }

    /**
     * Return pay url
     */
    function getPayUrl(): string
    {
        return "";
    }

    function createRequest(): void
    {
        $bankingId = 0;
        if ($this->bankRegister) {
            $bankingId = $this->bankRegister->banking_list_id;
        }
        $this->order->merchant_code = $merchantCode = $this->merchant->code ?? null;
        $this->order->banking_code = (!empty($merchantCode) && $merchantCode != PgwPaymentMerchants::MERCHANT_TRANSFER) ? '' : ($this->bank['code'] ?? null);

        if (!$this->request) {
            if ($this->merchant && $this->order) {
                $paymentRequestId = $this->order->payment_request_id ?? null;
                if (!empty($paymentRequestId)) {
                    $currentRequest = PgwPaymentRequest::find($paymentRequestId);
                    $currentRequest->merchant_id = $this->merchant->id;
                    $currentRequest->banking_id = $bankingId;
                    $currentRequest->save();
                } else {
                    $return_url_true = '';
                    $return_url_false = '';
                    if (!empty($this->order->return_url_true)) {
                        if (!empty(parse_url($this->order->return_url_true)['query'])) {
                            $return_url_true = $this->order->return_url_true . "&bill=" . $this->order->bill_code;
                        } else {
                            $return_url_true = $this->order->return_url_true . "?bill=" . $this->order->bill_code;
                        }
                    }
                    if (!empty($this->order->return_url_false)) {
                        if (!empty(parse_url($this->order->return_url_false)['query'])) {
                            $return_url_false = $this->order->return_url_false . "&bill=" . $this->order->bill_code;
                        } else {
                            $return_url_false = $this->order->return_url_false . "?bill=" . $this->order->bill_code;
                        }
                    }
                    $this->request = new PgwPaymentRequest();
                    $this->request->merchant_id = $this->merchant->id;
                    $this->request->partner_code = $this->order->partner_code;
                    $this->request->order_client_id = $this->order->id;
                    $this->request->banking_id = $bankingId;
                    $this->request->payment_value = $this->order->amount;
                    $this->request->total_pay = 0;
                    $this->request->url_return_true = $return_url_true;
                    $this->request->url_return_false = $return_url_false;
                    $this->request->url_return_api = env('PGW_PAYGATE_RETURN_URL') ?? $this->order->url_return_api;
                    $this->request->custom = $this->order->custom;
                    $this->request->created_at = time();
                    $this->request->updated_at = time();
                    $this->request->save();
                    $this->order->payment_request_id = $this->request->id;
                    $currentRequest = $this->request;
                }
                $this->setRequest($currentRequest);
            }
        }
        $this->order->save();
    }

    function createRequestMerchant(): void
    {
        $bankingId = 0;
        if ($this->bankRegister) {
            $bankingId = $this->bankRegister->banking_list_id;
        }
        if (!$this->requestMerchant) {
            if ($this->merchant && $this->order && $this->request) {
                if ($this->merchant->type == 'transfer') {
                    $currentRequestMerchant = PgwPaymentRequestMerchant::where('payment_request_id', $this->request->id)
                        ->where('order_client_id', $this->order->id)
                        ->where('merchant_id', $this->merchant->id)
                        ->where('banking_id', $bankingId)
                        ->first();
                    if ($currentRequestMerchant && !$currentRequestMerchant->respon_code) {
                        $this->setRequestMerchant($currentRequestMerchant);
                    }
                }

                if (!$this->requestMerchant) {
                    $this->requestMerchant = new PgwPaymentRequestMerchant();
                    $this->requestMerchant->merchant_id = $this->merchant->id;
                    $this->requestMerchant->order_client_id = $this->order->id;
                    $this->requestMerchant->payment_request_id = $this->request->id;
                    $this->requestMerchant->banking_id = $bankingId;
                    $this->requestMerchant->transaction_status = PgwPaymentRequestMerchant::TRANSACTION_STATUS_NO;
                    $this->requestMerchant->paid_status = PgwPaymentRequestMerchant::PAID_STATUS_UNSUCCESS;
                    $this->requestMerchant->remote_address = $_SERVER['SERVER_ADDR'];
                    $this->requestMerchant->web_browse = $this->getBrowserName();
                    $this->requestMerchant->save();

                    $this->genTransactionCode();
                }
            }
        }
    }

    function updateRequestMerchant($data): void
    {
        if ($this->requestMerchant) {
            $this->requestMerchant->description = json_encode((object)$data);
            $this->requestMerchant->save();
        }
    }

    function genTransactionCode()
    {
        if ($this->order && $this->requestMerchant && $this->merchant) {
            $code = $this->order->code;
            if ($this->merchant->type != 'transfer') {
                if ($this->order->partner_code) {
                    $code = $this->order->partner_code . '_';
                }
                $code .= $this->order->code . '_';
                $code .= str_pad($this->requestMerchant->id, 4, '0', STR_PAD_LEFT);
            } else {
                $code = $this->numHash(PgwPaymentRequestMerchant::LENGTH_NUMBER_RANDOM, $this->bankRegister->code, $this->bank->code, PgwPaymentRequestMerchant::TRIES_GET_NUMBER_RANDOM);
            }
            $this->requestMerchant->vpc_MerchTxnRef = $code;
            $this->requestMerchant->save();
        }
    }

    function numHash($len = null, $bankRegisterCode =null , $banking = null, $tries = null)
    {
        if (empty($len) || empty($banking) || empty($bankRegisterCode)) {
            throw new InvalidArgumentException("Không thể tạo được mã giao dịch giữa payment gateway với ngân hàng!");
        }
        $getCodeRandom = RandomHelper::int_code_random($len);

        if ($banking == 'BIDV') {
            $code = $getCodeRandom;
        } else {
            $code = $bankRegisterCode . $getCodeRandom;
        }
        $checkRequestMerchantCode = PgwPaymentRequestMerchant::where('vpc_MerchTxnRef', $code)->first();
        if (empty($checkRequestMerchantCode) && !empty($banking)) {
            return $code;
        }
        if ($tries == 0) {
            throw new InvalidArgumentException("Không thể tạo được mã giao dịch giữa payment gateway với ngân hàng. Vui lòng thử lại!");
        }
        return $this->numHash($len, $bankRegisterCode , $banking, $tries - 1);

    }

    function getTransactionCode()
    {
        if ($this->requestMerchant) {
            return $this->requestMerchant->vpc_MerchTxnRef;
        }

        return "";
    }

    function getAmount(): int
    {
        if ($this->order) {
            return intval($this->order->amount);
        }

        return 0;
    }

    function getBrowserName(): string
    {
        $t = strtolower($_SERVER['HTTP_USER_AGENT']);
        $t = " " . $t;
        if (strpos($t, 'opera') || strpos($t, 'opr/')) {
            return 'Opera';
        } elseif (strpos($t, 'edge')) {
            return 'Edge';
        } elseif (strpos($t, 'chrome')) {
            return 'Chrome';
        } elseif (strpos($t, 'safari')) {
            return 'Safari';
        } elseif (strpos($t, 'firefox')) {
            return 'Firefox';
        } elseif (strpos($t, 'msie') || strpos($t, 'trident/7')) {
            return 'Internet Explorer';
        }

        return 'Unkown';
    }

    /**
     * Update paid status order
     */
    function confirm()
    {

    }

    /**
     * Notify result payment for user
     */
    function notify()
    {

    }

    /**
     * Verify bill
     */
    public function getBill()
    {
        return [];
    }

    function parseGateData(array $params)
    {
        foreach ($params as $k => $v) {
            if (!is_array($v)) {
                $params[$k] = trim($v);
            }
        }
        $this->payGateData = $params;
    }

    function createPayLog($type)
    {
        $this->payLog = new PgwPaymentPayLog();
        $this->payLog->type = $type;
        $this->payLog->merchant_invoice = $this->getMerchantInvoice();
        $this->payLog->description = json_encode((object)$this->payGateData);
        $this->payLog->paid_status = PgwPaymentPayLog::PAID_STATUS_UNSUCCESS;
        $this->payLog->created_at = time();
        $this->payLog->save();
    }

    function updatePayLog($response, $success = false)
    {
        if ($this->payLog) {
            $this->payLog->response = json_encode((object)$response);
            $this->payLog->paid_status = $success ? PgwPaymentPayLog::PAID_STATUS_SUCCESS : PgwPaymentPayLog::PAID_STATUS_UNSUCCESS;

            if ($this->order) {
                $this->payLog->order_id = intval($this->order->id);
                $this->payLog->paid_value = $this->getAmount();
            }

            if ($this->requestMerchant) {
                $this->payLog->merchant_id = $this->requestMerchant->merchant_id;
                $this->payLog->banking_id = $this->requestMerchant->banking_id;
                $this->payLog->payment_request_merchant_id = $this->requestMerchant->id;
            }

            $this->payLog->save();
        }
    }

    function paymentSuccess($status = false)
    {
        if ($this->requestMerchant) {
            $this->requestMerchant->transaction_status = PgwPaymentRequestMerchant::TRANSACTION_STATUS_YES;
            $this->requestMerchant->respon_code = $this->payGateResponseCode();
            $this->requestMerchant->paid_status = $status ? PgwPaymentRequestMerchant::PAID_STATUS_SUCCESS : PgwPaymentRequestMerchant::PAID_STATUS_UNSUCCESS;
            $this->requestMerchant->updated_at = time();
            $this->requestMerchant->save();
        }

        if ($this->request) {
            $this->request->paid_status = $status ? PgwPaymentRequest::PAID_STATUS_SUCCESS : PgwPaymentRequest::PAID_STATUS_UNSUCCESS;
            $this->request->transsion_id = $this->getMerchantInvoice();
            $this->request->total_pay = $this->getAmount();
            $this->request->updated_at = time();
            $this->request->custom = $this->order->custom ?? '';
            $this->request->merchant_id = $this->requestMerchant->merchant_id ?? null;
            $this->request->banking_id = !empty($this->requestMerchant->banking_id) ? $this->requestMerchant->banking_id : PgwPaymentRequest::BANKING_ID_DEFAULT;
            $this->request->save();
        }
        $active_code = $this->getActiveCode($this->order);
        //Gửi thông báo lên firebase để giao diện xác nhận thanh toán khi thanh toán bằng ngân hàng
        if (!empty($this->order->merchant_code) && $this->order->merchant_code == PgwOrder::MERCHANT_TRANSFER) {
            $paramFirebaseNotification = [
                'RspCode' => '00',
                'message' => 'Success',
                'order_url_return_true' => $this->order->return_url_true.'&code='.$active_code ?? false,
                'order_url_return_false' => $this->order->return_url_false ?? false
            ];
            $urlFirebaseNotification = 'payment/ntf-payment-' . $this->order->bill_code;
            $setDataFireBase = PushDataFireBase::dispatch($paramFirebaseNotification, $urlFirebaseNotification);
        }
        //Gửi SMS khi đơn hàng ở trạng thái paid
        if ($status) {
            // Kiểm tra nếu paymentRequest có url_return_api thì gửi request về client
            if ($this->request->url_return_api) {
                $merchant = PgwPaymentMerchants::find($this->request->merchant_id);
                $banking = !empty($this->request->banking_id) ? PgwBanks::find($this->request->banking_id) : null;
                $bankRegister = PgwPartnerRegistriBanking::where('partner_code', $this->order->partner_code)->where('banking_list_id', $this->requestMerchant->banking_id)->first();
                $pushOrder = PushPgwPaymentRequest::dispatch($this->request, $status, $merchant, $this->requestMerchant, $bankRegister, $banking);
            }
            $enable_send_sms = config('payment.ENABLE_SEND_SMS');
            if ($enable_send_sms) {
                $this->sendSms($this->order);
            }
        }

        if (!empty($this->order->contact)) {
            $pushReserveContact = PushContactToCrm::dispatch(ContactLead::find($this->order->contact->contact_lead_id));
        }
    }

    function validHashData($validData, $data): array
    {
        $hashData = [];
        foreach ($validData as $key) {
            if (isset($data[$key])) {
                $hashData[] = $data[$key];
            }
        }
        return $hashData;
    }

    function createChecksum($params): string
    {
        return "";
    }

    function validChecksum($hashData, $checksum): bool
    {
//        dd($this->createChecksum($hashData));
        return $this->createChecksum($hashData) == $checksum;
    }

    function initOrder($vpcMerchTxnRef)
    {
        $this->requestMerchant = PgwPaymentRequestMerchant::where('vpc_MerchTxnRef', $vpcMerchTxnRef)->first();
        if ($this->requestMerchant) {
            $this->request = PgwPaymentRequest::find($this->requestMerchant->payment_request_id);
            $this->order = PgwOrder::find($this->requestMerchant->order_client_id);
            if ($this->order) {
                $this->customer = ContactLeadProcess::find($this->order->contact_lead_process_id);
            }
        }
    }

    function payBill()
    {
        return [];
    }

    function payGateVpcMerchTxnRef(): string
    {
        return "";
    }

    function getMerchantInvoice(): string
    {
        return "";
    }

    function doRequest($endpoint, $data, $method = 'POST')
    {

    }

    function payGateResponseCode()
    {
        return "0";
    }

    function result()
    {
        $active_code = $this->getActiveCode($this->order);
        $checkReturnTrue = !empty($this->order->return_url_true);
        $checkReturnFalse = !empty($this->order->return_url_false);
        if ($this->requestMerchant && $this->request) {
            if (!empty($this->successPayment) && $checkReturnTrue == true) {
                $returnUrl = $this->request->url_return_true.'&code='.$active_code;
            } elseif ($checkReturnFalse) {
                $returnUrl = $this->request->url_return_false;
            }
        }
        return json_encode([
            'success' => $this->successPayment ?? null,
            'returnCancelUrl' => $this->cancelURL ?? null,
            'returnUrl' => $returnUrl ?? null,
        ]);
    }

    public function getQRUrl(): string
    {
        return "";
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config): void
    {
        $this->config = (object)$config;
    }

    /**
     * @return PgwOrder
     */
    public function getOrder(): PgwOrder
    {
        return $this->order;
    }

    /**
     * @param PgwOrder $order
     */
    public function setOrder(PgwOrder $order): void
    {
        $this->order = $order;
    }

    /**
     * @return PgwPaymentMerchants
     */
    public function getMerchant(): PgwPaymentMerchants
    {
        return $this->merchant;
    }

    /**
     * @param PgwPaymentMerchants $merchant
     */
    public function setMerchant(PgwPaymentMerchants $merchant): void
    {
        $this->merchant = $merchant;
    }

    /**
     * @return PgwBankingList|null
     */
    public function getBank(): ?PgwBankingList
    {
        return $this->bank;
    }

    /**
     * @param PgwBankingList|null $bank
     */
    public function setBank(?PgwBankingList $bank): void
    {
        $this->bank = $bank;
    }

    /**
     * @return PgwPaymentRequest
     */
    public function getRequest(): PgwPaymentRequest
    {
        return $this->request;
    }

    /**
     * @param PgwPaymentRequest $request
     */
    public function setRequest(PgwPaymentRequest $request): void
    {
        $this->request = $request;
    }

    /**
     * @return PgwPaymentRequestMerchant
     */
    public function getRequestMerchant(): PgwPaymentRequestMerchant
    {
        return $this->requestMerchant;
    }

    /**
     * @param PgwPaymentRequestMerchant $requestMerchant
     */
    public function setRequestMerchant(PgwPaymentRequestMerchant $requestMerchant): void
    {
        $this->requestMerchant = $requestMerchant;
    }

    /**
     * @return ContactLeadProcess
     */
    public function getCustomer(): ContactLeadProcess
    {
        return $this->customer;
    }

    /**
     * @param ContactLeadProcess $customer
     */
    public function setCustomer(ContactLeadProcess $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return PgwPartnerRegistriBanking|null
     */
    public function getBankRegister(): ?PgwPartnerRegistriBanking
    {
        return $this->bankRegister;
    }

    /**
     * @param PgwPartnerRegistriBanking|null $bankRegister
     */
    public function setBankRegister(?PgwPartnerRegistriBanking $bankRegister): void
    {
        $this->bankRegister = $bankRegister;
    }

    public function sendSms(PgwOrder $order)
    {
        $smsContent = "Chi con 1 buoc de bat dau hoc tap tai Hocmai.vn. Truy cap {link} de thanh toan voi uu dai hap dan. Hotline ho tro {hotline}";
        $hotline = '19006933';
        $landingpage = $order->landingPage;
        if ($landingpage) {
            if ($landingpage->hotline) {
                $hotline = $landingpage->hotline;
            }
            if ($landingpage->purpose == 'reserve') {
                $template = MessageTemplate::where('code', 'order_sms_code_resevers')->first();
            } else {
                $template = MessageTemplate::where('code', 'order_sms_active_code')->where('event', $landingpage->event)
                    ->first();
            }
            if ($template->content ?? '') {
                $smsContent = $template->content;
            }
        }
        $active_code = $order->active_code;
        //Nếu đơn hàng là mã coupon
        $couponCode = "";
        if ($landingpage->purpose == 'reserve') {
            $couponCode = $this->getCouponCode($order, $landingpage);
            if ($couponCode) {
                $active_code = $couponCode;
            }
        }
        $smsContent = str_replace('{active_code}', $active_code, $smsContent);
        $smsContent = str_replace('{hotline}', $hotline, $smsContent);
        if ($landingpage->is_send_sms_paid === 'yes' && $active_code) {
            $sendSms = SendSms::send($order->contact->phone, $smsContent, $order->id, get_class((object)$order), $order->contact_lead_process_id);
        }

        //update contact reserve
        if ($couponCode) {
            $this->updateContactReserveLog($order->contact_lead_process_id, $landingpage->id, $couponCode, $smsContent);
        }

    }

    public function sendMail($landingPage)
    {
        $emailContent = '<body>
                             <p>Mã active_code của landing_page<strong>(' . $landingPage['id'] . ') : ' . $landingPage['domain_name'] . ' </strong>  sắp hết!</strong><br>
                             <br><p>Vui lòng thêm số lượng vào bảng "active_code" trong cơ sở dữ liệu</p>
                             </body>';
        $sendEmailParams = [
            'landing_page_id' => 104,
            'contact_lead_process_id' => 1,
            'from_email' => 'noreply@hocmai.vn',
            'from_name' => 'HOCMAI',
            'to_email' => config('hocmai.ADMIN.email'),
            'to_name' => config('hocmai.ADMIN.full_name'),
            'subject' => 'THÔNG BÁO MÃ ACTIVE_CODE SẮP HẾT',
            'content' => $emailContent,
            'file_attach' => 'nullable|string',
        ];
        $email = EmailSave::create($sendEmailParams);
        SendEmailHocMai::dispatch($email);
    }

    public function getCouponCode($order, $landingpage)
    {
        $tokenJwt = JWT::encode([
            'event' => $landingpage->event,
            'phone' => $order->contact->phone
        ], config('hocmai.COUPON_KEY'), 'HS256');

        $api_coupon = config('hocmai.API_COUPON');
        $getCoupon = Request::post($api_coupon, [
            'headers' => [
                'TOKEN' => $tokenJwt,
            ],
            'json' => [
                'event' => $landingpage->event,
                'phone' => $order->contact->phone
            ]
        ]);

        $response = json_decode($getCoupon->getBody());
        if ($response->status == 'success') {
            if (isset($response->data->code)) {
                $couponCode = $response->data->code;
                $order->code_reverse = $couponCode;
                $order->save();
                return $couponCode;
            }
        }

        return false;
    }

    public function getActiveCode($order){
        //Kiểm tra nếu đơn hàng đã có active_code thì trả về
        $check_active_code = RedisHelper::get('active_code'.$order->id);
        if (!empty($check_active_code)){
            return $check_active_code;
        }
        $landingpage = $order->landingPage;
        //Kiểm tra số lượng active_code còn lại theo LDP để gửi thông báo qua mail
        if (!empty(env('ENABLE_COUNT_WARNING_ACTIVE_CODE'))) {
            $countActiveCode = ActiveCode::where('landing_page_id', $landingpage->id)->where('used', ActiveCode::USED_NO)->count();
            if (ActiveCode::COUNT_WARNING_ACTIVE_CODE_START <= $countActiveCode && $countActiveCode<= ActiveCode::COUNT_WARNING_ACTIVE_CODE_FINISH) {
                //Nếu mã active_code còn dưới 30 thì khi sử dụng 5 lượt sẽ gửi email cảnh báo 1 lần
                if ($countActiveCode % ActiveCode::COUNT_WARNING_ACTIVE_CODE_DIVIDE == 0) {
                    $this->sendMail($landingpage);
                }
            }
        }

        $active_code = $order->partner_code . "-" . $order->code;
        $countActiveCodeProduct = ActiveCode::where('landing_page_id', $landingpage->id)->where('product_id',ActiveCode::PRODUCT_ID_DEFAULT)->count();
        $getActiveCode = ActiveCode::where('landing_page_id', $landingpage->id)->where('used', ActiveCode::USED_NO)->first();
        if ($countActiveCodeProduct == intval(ActiveCode::PRODUCT_ID_DEFAULT)){
            $orderDetail = $order->orderDetail->first();
            $productID = $orderDetail->product_id ?? ActiveCode::PRODUCT_ID_DEFAULT;
            $getActiveCode = ActiveCode::where('landing_page_id', $landingpage->id)->where('used', ActiveCode::USED_NO)->where('product_id',$productID)->first();
        }
        if (!empty($getActiveCode)) {
            $getActiveCode->used = ActiveCode::USED_YES;
            $getActiveCode->save();
            $active_code = !empty($getActiveCode) ? $getActiveCode->code : $active_code;
        }
        RedisHelper::set('active_code'.$order->id,$active_code,ActiveCode::EXPIRED_REDIS_ACTIVE_CODE);
        $this->order->active_code = $active_code;
        $this->order->save();
        return $active_code;
    }

    public function updateContactReserveLog($contactLeadProcessId, $landingpageID, $couponCode, $smsContent = '')
    {
        $checkLog = ContactLeadProcessReserveLog::where('contact_lead_process_id', $contactLeadProcessId)->where('landing_page_id', $landingpageID)->orderBy('id', 'desc')->first();
        if ($checkLog) {
            $checkLog->status = 'sent_sms_reserve';
            $checkLog->coupon_code = $couponCode;
            $checkLog->sms_content = $smsContent ?? '';
            $checkLog->save();
            return $checkLog;
        }

        return false;
    }

    public function cancelPaygate($bill_code)
    {
        $domain = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $returnUrl = $domain . '/payment/pgw?bill=' . $bill_code;
        return $returnUrl;
    }


}
