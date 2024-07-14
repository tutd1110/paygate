<?php

namespace App\Repositories\PGW;

use App\Helper\Mycurl;
use App\Helper\Request;
use App\Jobs\PushOrderRequest\pushCancelVaMsb;
use App\Jobs\PushOrderRequest\PushOrderRequest;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPaymentMerchants;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPaymentRequestMerchant;
use App\Payment\Handle\MomoHandle;
use App\Payment\Handle\TransferBIDVHandle;
use App\Payment\Handle\VnpayHandle;
use Carbon\Carbon;
use App\Exceptions\InvalidArgumentException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helper\RandomHelper;

class PgwOrderRepository implements PgwOrderInterface
{
    const RSP_CODE_UPDATE_ORDER_PAID = "00";
    const ORDER_STATUS_CANCEL = 'cancel';
    const PAYMENT_STATUS_UNSUCCESS = 'unsuccess';
    private $pgwOrderModel;

    public function __construct()
    {
        $this->pgwOrderModel = app()->make(PgwOrder::class);
    }

    /***
     * Tạo đơn hàng
     */

    public function create($data)
    {
        $data['bill_code'] = Str::uuid();
        $data['code'] = RandomHelper::code_random(6);


        try {

            $pgwOrder = DB::transaction(function () use ($data) {
                $sumMoney = 0;
                $couponDiscount = 0;
                // check discount
                if (!empty($data['discount'])) {
                    $totalDiscount = $data['discount'];
                }
                $data['quantity'] = collect($data['item_quantity'] ?? [])->sum();
                $data['quantity'] = !empty($data['quantity']) ? $data['quantity'] : 1;
                /***
                 * @var $pgwOrder PgwOrder
                 */

                $pgwOrder = PgwOrder::create($data);
                $checkCouponArray = [];
                foreach ($data['item_product_id'] as $key => $eachItem) {
                    $newItem = $pgwOrder->orderDetail()->create([
                        'product_id' => $eachItem,
                        'product_type' => $data['item_product_type'][$key],
                        'product_name' => $data['item_product_name'][$key] ?? '',
                        'quantity' => $data['item_quantity'][$key] ?? 1,
                        'price' => $data['item_price'][$key],
                        'discount' => $data['item_discount'][$key] ?? 0,
                    ]);

                    $sumMoney += (($newItem->price - $newItem->discount) * $newItem->quantity);
                    $checkCouponArray[] = [
                        'product_id' => strval($data['item_product_id'][$key]),
                        'product_type' => $data['item_product_type'][$key],
                    ];
                }
                //check coupon
                if (!empty($data['coupon_code'])) {
                    $couponDiscount = $this->checkCoupounCode($checkCouponArray, $data['coupon_code']);
                    if ($couponDiscount['status'] == 'success') {
                        $couponDiscount = $couponDiscount['cart'] ? $couponDiscount['cart']['total_discount'] : 0;
                    } else {
                        $couponDiscount = 0;
                    }
                }
                $discount = !empty($couponDiscount) ? $couponDiscount : $pgwOrder->discount;
                $amount = $sumMoney - $discount;
                $pgwOrder->amount = ($amount > 0) ? $amount : 0;

                /****
                 * Khởi tạo payment request
                 */
                if (!empty($pgwOrder)) {
                    $return_url_true = '';
                    $return_url_false = '';
                    if (!empty($pgwOrder->return_url_true)) {
                        if (!empty(parse_url($pgwOrder->return_url_true)['query'])) {
                            $return_url_true = $pgwOrder->return_url_true . "&bill=" . $pgwOrder->bill_code;
                        } else {
                            $return_url_true = $pgwOrder->return_url_true . "?bill=" . $pgwOrder->bill_code;
                        }
                    }
                    if (!empty($pgwOrder->return_url_false)) {
                        if (!empty(parse_url($pgwOrder->return_url_false)['query'])) {
                            $return_url_false = $pgwOrder->return_url_false . "&bill=" . $pgwOrder->bill_code;
                        } else {
                            $return_url_false = $pgwOrder->return_url_false . "?bill=" . $pgwOrder->bill_code;
                        }
                    }

                    $paramPaymentRequest = [
                        'partner_code' => $pgwOrder->partner_code,
                        'order_client_id' => $pgwOrder->id,
                        'paid_status' => PgwOrder::PAID_STATUS_UNSUCCESS,
                        'payment_value' => $pgwOrder->amount,
                        'url_return_true' => $return_url_true,
                        'url_return_false' => $return_url_false,
                        'url_return_api' => env('PGW_PAYGATE_RETURN_URL') ?? $pgwOrder->url_return_api,
                        'custom' => $pgwOrder->custom,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                    $paymentRequest = PgwPaymentRequest::create($paramPaymentRequest);
                }

                $pgwOrder->payment_request_id = $paymentRequest['id'];
                $contactLeadProcess = $pgwOrder->contact;
                $orderDetail = $pgwOrder->orderDetail;
                $pgwOrder->save();
                $contactLeadProcess->description = 'Người dùng thanh toán đơn hàng: ' . $this->getHocMaiPayLink($pgwOrder);
                $contactLeadProcess->save();

                $pgwOrder->makeVisible('code');
                return $pgwOrder;
            });
            return $pgwOrder;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function checkCoupounCode($checkCouponArray = [], $couponCode = '')
    {
        $api_coupon = config('hocmai.API_COUPON_CHECK');
        $token = config('hocmai.KEY_API_COUPON_CHECK');
        $option = [
            'headers' => [
                'TOKEN' => $token,
            ],
            'json' => [
                "products" => $checkCouponArray,
                "voucher" => $couponCode,
            ]
        ];
        $getCoupon = Mycurl::postCurl($api_coupon, $option);
        return $getCoupon;
    }


    public function getOrders($params = [])
    {
        $filter = Arr::only($params, [
            'getLandingPage', 'getContact', 'getOrderDetail', 'getPartner'
            , 'getPaymentMerchant', 'getBanking', 'getContactReserveLog', 'getPaymentRequest',
            'id', 'code', 'partner_code', 'amount_first', 'amount_end', 'quantity',
            'start_date', 'end_date', 'landing_page_id', 'status', 'merchant_code',
            'banking_code', 'order_by', 'direction', 'export', 'order_client_id',
            'getPaymentRequestMerchant', 'vpc_MerchTxnRef'
        ]);
        $query = $this->pgwOrderModel::query();
        if (isset($filter['getLandingPage'])) {
            $query = $query->with('landingPage');
        }
        if (isset($filter['getContact'])) {
            $query = $query->with('contact');
        }
        if (isset($filter['getOrderDetail'])) {
            $query = $query->with('orderDetail');
        }
        if (isset($filter['getPartner'])) {
            $query = $query->with('partner');
        }
        if (isset($filter['getPaymentMerchant'])) {
            $query = $query->with('paymentMerchant');
        }
        if (isset($filter['getBanking'])) {
            $query = $query->with('banking');
        }
        if (isset($filter['getContactReserveLog'])) {
            $query = $query->with('contactLeadProcessReserveLogs');
        }
        if (isset($filter['getPaymentRequest'])) {
            $query->with(['paymentRequest' => function ($query) use ($filter) {
                $query->with('requestMerchant');
            }]);
        }
        if (isset($filter['getPaymentRequestMerchant'])) {
            $query->with(['paymentRequestMerchant' => function ($query) use ($filter) {
                $query->orderBy('updated_at', 'desc');
            }]);
        }
        if (isset($filter['id'])) {
            if (is_array($filter['id'])) {
                $query = $query->whereIn('id', $filter['id']);
            } else {
                $query = $query->where('id', $filter['id']);
            }
        }
        if (isset($filter['landing_page_id'])) {
            if (is_array($filter['landing_page_id'])) {
                $query = $query->whereIn('landing_page_id', $filter['landing_page_id']);
            } else {
                $query = $query->where('landing_page_id', $filter['landing_page_id']);
            }
        }
        if (isset($filter['vpc_MerchTxnRef'])) {
            $query = $query->whereHas('paymentRequestMerchant', function ($query) use ($filter) {
                if (is_array($filter['vpc_MerchTxnRef'])) {
                    $query->whereIn('vpc_MerchTxnRef', $filter['vpc_MerchTxnRef']);
                } else {
                    $query->where('vpc_MerchTxnRef', $filter['vpc_MerchTxnRef']);
                }
            });
        }
        if (isset($filter['order_client_id'])) {
            if (is_array($filter['order_client_id'])) {
                $query = $query->whereIn('order_client_id', $filter['order_client_id']);
            } else {
                $query = $query->where('order_client_id', $filter['order_client_id']);
            }
        }
        if (isset($filter['code'])) {
            if (is_array($filter['code'])) {
                $query = $query->whereIn('code', $filter['code']);
            } else {
                $query = $query->where('code', $filter['code']);
            }
        }
        if (isset($filter['partner_code'])) {
            if (is_array($filter['partner_code'])) {
                $query = $query->whereIn('partner_code', $filter['partner_code']);
            } else {
                $query = $query->where('partner_code', $filter['partner_code']);
            }
        }
        if (isset($filter['status'])) {
            if (is_array($filter['status'])) {
                $query = $query->whereIn('status', $filter['status']);
            } else {
                $query = $query->where('status', $filter['status']);
            }
        }
        if (isset($filter['merchant_code'])) {
            if (is_array($filter['merchant_code'])) {
                $query = $query->whereIn('merchant_code', $filter['merchant_code']);
            } else {
                $query = $query->where('merchant_code', $filter['merchant_code']);
            }
        }
        if (isset($filter['banking_code'])) {
            if (is_array($filter['banking_code'])) {
                $query = $query->whereIn('banking_code', $filter['banking_code']);
            } else {
                $query = $query->where('banking_code', $filter['banking_code']);
            }
        }
        if (isset($filter['amount_first'])) {
            $query = $query->where('amount', '>', $filter['amount_first']);
        }
        if (isset($filter['amount_end'])) {
            $query = $query->where('amount', '<', $filter['amount_end']);
        }
        if (isset($filter['start_date'])) {
            $query = $query->where('created_at', '>=', Carbon::createFromFormat('Y-m-d', $filter['start_date'])->startOfDay()->format('Y-m-d H:i:s'));
        }
        if (isset($filter['end_date'])) {
            $query = $query->where('created_at', '<=', Carbon::createFromFormat('Y-m-d', $filter['end_date'])->endOfDay()->format('Y-m-d H:i:s'));
        }
        if (isset($filter['order_by'])) {
            if (is_array($filter['order_by'])) {
                foreach ($filter['order_by'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            } else {
                $filter['order_by'] = explode(',', $filter['order_by']);
                $filter['direction'] = explode(',', $filter['direction']);
                foreach ($filter['order_by'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            }
        }
        if (empty($filter['export'])) {
            $limit = isset($params['limit']) ? $params['limit'] : 20;
            $orders = $query->paginate($limit);
        } else {
            $orders = $query->get();
            return $orders;
        }
        return $orders->setCollection($orders->getCollection()->makeVisible('created_at'));
    }

    public function updateStatusClient($id)
    {
        try {
            $orders = $this->pgwOrderModel::find($id);
            $paymentRequest = PgwPaymentRequest::find($orders->payment_request_id);
            if (empty($orders)) {
                throw new \Exception('Đơn hàng không tồn tại');
            }
            if (empty($paymentRequest)) {
                throw new \Exception('Không tìm thấy thông tin thanh toán của đơn hàng');
            }
            if (empty($orders->url_return_api)) {
                throw new \Exception('Đơn hàng không có link callback về CRM');
            }
            if ($orders->status != PgwOrder::STATUS_PAID) {
                throw new \Exception('Đơn hàng chưa thanh toán thành công');
            }
            if ($paymentRequest->paid_status != PgwPaymentRequest::PAID_STATUS_SUCCESS) {
                throw new \Exception('Đơn hàng chưa thanh toán thành công trên cổng thanh toán');
            }
            if ($orders->order_status != PgwOrder::ORDER_CLIENT_STATUS_NOT_PAID) {
                throw new \Exception('Đơn hàng đã được gửi thông tin về CRM');
            }
            $paymentRequest['merchant_code'] = $orders['merchant_code'] ?? null;
            $paymentRequest['banking_code'] = $orders['banking_code'] ?? null;
            PushOrderRequest::dispatch($orders, $paymentRequest);
            return [
                'status' => true,
                'message' => 'Cập nhật thành công!'
            ];
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'message' => $exception->getMessage()
            ];
        }


    }

    public function update($id, $data)
    {
        try {
            $order = PgwOrder::with('contact')->find($id);
//            $order = DB::transaction(function () use ($order, $data) {
            $sumMoney = 0;
            $couponDiscount = 0;
            if (!empty($data['coupon_code'])) {
                $couponDiscount = $this->checkCoupounCode($data['coupon_code']);
            }
            $order->fill($data);
            // $order->orderDetail;
            if ($data['item_product_id'] ?? null) {
                $order->orderDetail()->delete();
                foreach ($data['item_product_id'] as $key => $eachItem) {
                    $item = $order->orderDetail()->create([
                        'product_id' => $eachItem,
                        'product_type' => $data['item_product_type'][$key],
                        'product_name' => $data['item_product_name'][$key] ?? '',
                        'quantity' => $data['item_quantity'][$key],
                        'price' => $data['item_price'][$key],
                        'discount' => $data['item_discount'][$key],
                    ]);
                    $sumMoney += (($item->price - $item->discount) * $item->quantity);
                }
                $discount = $order->discount ?? $couponDiscount;
                $amount = $sumMoney - $discount;
                $order->amount = ($amount > 0) ? $amount : 0;
                $order->orderDetail;
            }
            $order->banking_code = (!empty($data['merchant_code']) && $data['merchant_code'] != PgwPaymentMerchants::MERCHANT_TRANSFER) ? '' : ($data['banking_code'] ?? null);
            // Nếu cập nhật là cancel thì gửi request thông báo đơn hàng bị huỷ cho client
            if ($order->status == self::ORDER_STATUS_CANCEL && !empty($order->url_return_api)) {
                $paymentRequest['total_pay'] = $order['amount'];
                $paymentRequest['paid_status'] = self::ORDER_STATUS_CANCEL;
                $paymentRequest['merchant_code'] = $order['merchant_code'] ?? '';
                PushOrderRequest::dispatch($order, $paymentRequest);
            }
            /** Nếu huỷ đơn hàng thì gửi request huỷ VA bên ngân hàng MSB */
            if ($order->status == self::ORDER_STATUS_CANCEL) {
                pushCancelVaMsb::dispatch($order);
            }
            $order->save();
            return $order;
//            });
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /***
     * @param Invoice $invoice
     *
     * @return bool
     */

    public function getHocMaiPayLink(PgwOrder $pgwOrder)
    {
        return "https://hocmai.vn/payment/quickpay/?bill={$pgwOrder->code}";
    }

    public function callback($pgwPaymentRequest)
    {
        try {
            $pgwOrder = $this->pgwOrderModel::find($pgwPaymentRequest['order_client_id']);
            $message = '';
            $status = false;
            if ($pgwOrder->order_status != PgwOrder::ORDER_CLIENT_STATUS_NOT_PAID) {
                $message = 'Đơn hàng đã được gửi thông tin về CRM';
                $status = true;
            }
            $signature = $this->checkSignature($pgwOrder, $pgwPaymentRequest);
            $pgwOrder['status'] = (!empty($signature)) ? PgwOrder::STATUS_PAID : PgwOrder::STATUS_FAIL;
            $pgwOrder['merchant_code'] = $pgwPaymentRequest['merchant_code'] ?? null;
            $pgwOrder['banking_code'] = $pgwPaymentRequest['banking_code'] ?? null;
            $pgwOrder->save();
            if (empty($signature)) {
                throw new \Exception('Chữ kí không hợp lệ');
            }
            if (!empty($pgwOrder->url_return_api)) {
                $message = 'Đơn hàng đã cập nhật thành công';
                $pushOrder = PushOrderRequest::dispatch($pgwOrder, $pgwPaymentRequest);
            }
            return [
                'status' => $status,
                'message' => $message
            ];
        }catch (\Exception $exception){
            return response()->json([
                'data' => [
                    'message' => $exception->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }

    /** Hàm kiểm tra chữ kí sau khi thanh toán */
    public function checkSignature($pgwOrder, $pgwPaymentRequest)
    {
        $order = [
            'partner_code' => $pgwOrder->partner_code,
            'order_client_id' => $pgwOrder->id,
            'total_pay' => intval($pgwOrder->amount),
        ];
        ksort($order);
        $ciphertextRaw = http_build_query($order, '', '&');
        $signature = hash_hmac('sha512', $ciphertextRaw, env('ORDER_ENCODE_KEY'));
        if ($pgwPaymentRequest['signature'] == $signature) {
            return true;
        } else {
            return false;
        }
    }

    public function pushOrderRequest($pgwOrder, $pgwPaymentRequest)
    {
        try {
            if ($pgwOrder->order_status != PgwOrder::ORDER_CLIENT_STATUS_NOT_PAID){
                return response()->json([
                    'data' => [
                        'message' => 'Đơn hàng đã được gửi thông tin về CRM',
                        'type' => 'fail',
                    ]
                ]);
            }
            if (!empty($pgwOrder) && !empty($pgwPaymentRequest)) {
                $params = [
                    'partner_code' => $pgwOrder->partner_code,
                    'order_client_id' => $pgwOrder->order_client_id,
                    'total_pay' => intval($pgwPaymentRequest['total_pay']),
                ];
                ksort($params);
                $params['signature'] = $this->enCryptData($params);
                $params['paid_status'] = $pgwPaymentRequest['paid_status'] ?? null;
                $params['merchant_code'] = $pgwPaymentRequest['merchant_code'] ?? null;
                $params['banking_code'] = $pgwPaymentRequest['banking_code'] ?? null;
                $params['vpc_MerchTxnRef'] = $pgwPaymentRequest['vpc_MerchTxnRef'] ?? null;
                $params['transsion_id'] = $pgwPaymentRequest['transsion_id'] ?? null;
                $params['custom'] = (!empty($pgwOrder->custom)) ? json_decode($pgwOrder->custom, true) : null;
                $response = Request::post($pgwOrder->url_return_api, [
                    'form_params' => $params
                ]);

                $checkResponse = json_decode((string)$response->getBody(), true);

                if ($checkResponse['status'] != PgwOrder::PAID_STATUS_SUCCESS) {
                    throw new \Exception('Cập nhật đơn hàng sang CRM thất bại');
                }
                $pgwOrder->order_status = PgwOrder::ORDER_CLIENT_STATUS_PAID;
                $pgwOrder->save();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function enCryptData($params = [])
    {
        $ciphertextRaw = http_build_query($params, '', '&');
        $signature = hash_hmac('sha512', $ciphertextRaw, env('ORDER_ENCODE_KEY'));
        return $signature;
    }

    public function updateOrderPaid($orders, $payGate)
    {
        try {
            $merchant_id = !empty($orders['merchant_code']) ? $orders['paymentMerchant']['id'] : null;
            $banking_id = !empty($orders['banking_code']) ? $orders['banking']['id'] : 0;
            $merchantBankingCode = !empty($orders['banking_code']) ? $orders['banking_code'] : $orders['merchant_code'];
            $paymentRequestMerchant = PgwPaymentRequestMerchant::query()
                ->where('order_client_id', $orders['id'])
                ->where('merchant_id', $merchant_id)
                ->where('banking_id', $banking_id)
                ->first();

            if (empty($paymentRequestMerchant)) {
                throw new \InvalidArgumentException('Đơn hàng chưa thực hiện giao dịch');
            }

            //Giả lập params và chữ kí để gửi yêu cầu thanh toán thành công sang cổng thanh toán
            $url_api_paybill = env('PGW_API', '') . "/api/v1/pgw/" . $merchantBankingCode . "/paybill";
            if ($merchantBankingCode == 'BIDV') {
                $url_api_paybill = env('PGW_API', '') . "/api/v1/pgw/transfer/paybill";
            }
            if ($merchantBankingCode == 'MSB') {
                $url_api_paybill = env('PGW_API', '') . "/api/v1/pgw/msb/callback";
            }

            $paramPaygate = $this->getParamMerchantBanking($merchantBankingCode, $orders, $paymentRequestMerchant, $payGate);
            $updateOrderPaid = Request::post($url_api_paybill, ['form_params' => $paramPaygate]);
            $updateOrderPaid = json_decode((string)$updateOrderPaid->getBody(), true);

            //Kiểm tra và xác nhận kết quả sau khi nhận được request trả về
            $checkUpdatePaid = false;
            if (!empty($merchantBankingCode) && !empty($updateOrderPaid['RspCode'])) {
                if ($updateOrderPaid['RspCode'] == self::RSP_CODE_UPDATE_ORDER_PAID) {
                    $checkUpdatePaid = true;
                }
            }
            return [
                'checkUpdateOrderPaid' => $checkUpdatePaid,
                'message' => empty($checkUpdatePaid) ? "Cập nhật thất bại" : "Cập nhật thành công"
            ];
        } catch (\InvalidArgumentException $exception) {
            return [
                'checkUpdateOrderPaid' => false,
                'message' => $exception->getMessage()
            ];
        } catch (\Exception $exception) {
            return [
                'checkUpdateOrderPaid' => false,
                'message' => $exception->getMessage()
            ];
        }
    }

    public function getParamMerchantBanking($merchantBankingCode, $orders, $paymentRequestMerchant, $payGate)
    {
        switch ($merchantBankingCode) {
            case 'momo' :
                $params = [
                    "accessKey" => $payGate->getConfig()->accessKey ?? config('payment.momo_v3.accessKey'),
                    "partnerCode" => $payGate->getConfig()->partnerCode ?? config('payment.momo_v3.partnerCode'),
                    "orderId" => $paymentRequestMerchant['vpc_MerchTxnRef'],
                    "requestId" => $paymentRequestMerchant['vpc_MerchTxnRef'],
                    "amount" => intval($orders['amount']),
                    "orderInfo" => "Hocmai thanh toan hoc phi",
                    "orderType" => $payGate->getConfig()->requestType ?? config('payment.momo_v3.requestType'),
                    "transId" => "0123456789",
                    "resultCode" => 0,
                    "message" => "Th\u00e0nh c\u00f4ng.",
                    "payType" => "qr",
                    "responseTime" => strtotime("now"),
                    "extraData" => $orders['bill_code'],
                ];
                $params["signature"] = $this->createChecksum('momo', $params, $payGate);
                return $params;
            case 'vnpay' :
                $params = [
                    "vnp_Amount" => $orders['amount'] * 100,
                    "vnp_BankCode" => "NCB",
                    "vnp_CardType" => "ATM",
                    "vnp_OrderInfo" => $orders['bill_code'],
                    "vnp_PayDate" => Carbon::now()->format('YmdHis'),
                    "vnp_ResponseCode" => "00",
                    "vnp_TmnCode" => $payGate->getConfig()->partnerCode ?? config('payment.vnpay.partnerCode'),
                    "vnp_TransactionNo" => "12345678",
                    "vnp_TransactionStatus" => "00",
                    "vnp_TxnRef" => $paymentRequestMerchant['vpc_MerchTxnRef']
                ];
                $params["vnp_SecureHash"] = $this->createChecksum('vnpay', $params, $payGate);
                return $params;
            case 'BIDV' :
                $params = [
                    "bill_id" => $paymentRequestMerchant->vpc_MerchTxnRef,
                    "trans_date" => Carbon::now()->format('YmdHis'),
                    "amount" => $orders->amount,
                    "service_id" => "00V001",
                    "trans_id" => Carbon::now()->format('YmdHis'),
                    "customer_id" => $paymentRequestMerchant->vpc_MerchTxnRef,
                ];
                $params["checksum"] = $this->createChecksum('BIDV', $params, $payGate);
                return $params;
            case 'MSB':
                $params = [
                    "tranSeq" => "MSB_" . $orders->code . '_' . date('dmY'),
                    "vaCode" => config('payment.msb.va_code'),
                    "vaNumber" => $paymentRequestMerchant->vpc_MerchTxnRef,
                    "vaName" => "CTCP DT VA DV GIAO DUC",
                    "fromAccountName" => !empty($orders->contact && $orders->contact->full_name) ? $orders->contact->full_name : null,
                    "fromAccountNumber" => "123456789",
                    "toAccountNumber" => "3201013533838",
                    "toAccountName" => "CTCP DT VA DV GIAO DUC",
                    "tranAmount" => intval($orders->amount),
                    "tranRemark" => $orders->code . ' ' . (!empty($orders->contact && $orders->contact->phone) ? $orders->contact->phone : null) . "chuyen khoan hoc phi - " . $paymentRequestMerchant->vpc_MerchTxnRef,
                    "tranDate" => date('d/m/Y'),
                ];

                $params["signature"] = $this->createChecksum('MSB', $params, $payGate);
                return $params;
            default :
                throw new InvalidArgumentException('Cổng thanh toán(Ngân hàng) chưa được hỗ trợ!');
        }
    }

    public function createCheckSum($merchantBankingCode, $params, $payGate)
    {
        switch ($merchantBankingCode) {
            case 'vnpay' :

                ksort($params);
                $hashData = http_build_query($params, '', '&');
                $check_sum = hash_hmac('sha512', $hashData, ($payGate->getConfig()->secretKey ?? config('payment.vnpay.secretKey')));
                return $check_sum;
            case 'momo' :
                ksort($params);
                $hash = [];
                foreach ($params as $key => $value) {
                    $hash[] = $key . '=' . $value;
                }
                return hash_hmac('sha256', implode('&', $hash), $payGate->getConfig()->secretKey ?? config('payment.momo_v3.secretKey'));
            case 'BIDV' :
                $check_sum_params = [
                    0 => $params['trans_id'],
                    1 => $params['bill_id'],
                    2 => $params['amount'],
                ];
                $secretKey = (isset($payGate->getConfig()->secretKey)) ? $payGate->getConfig()->secretKey : config('payment.transfer.secretKey');
                array_unshift($check_sum_params, $secretKey);
                return md5(implode('|', $check_sum_params));
            case 'MSB' :
                $check_sum_params = [
                    'sub' => $params['tranSeq'] . '|' . $params['tranDate'] . '|' . $params['vaNumber'] . '|' . $params['tranAmount'],
                    'iat' => strtotime("now"),
                    'exp' => strtotime("+1 week"),
                ];
                return base64_encode('{"alg":"RS256"}') . '.' . base64_encode(json_encode($check_sum_params));
            default :
                return null;
        }

    }

}
