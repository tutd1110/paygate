<?php

namespace App\Http\Controllers\Api\PGW;

use App\Exceptions\InvalidArgumentException;
use App\Exceptions\ResourceNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\ContactLeadProcess;
use App\Models\LandingPageTracking;
use App\Models\PGW\PgwBanks;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwOrderDetail;
use App\Models\PGW\PgwPartner;
use App\Models\PGW\PgwPaymentMerchants;
use App\Models\PGW\PgwPaymentRequestMerchant;
use App\Models\ThirdParty;
use App\Payment\PayGate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PgwPaymentController extends Controller
{
    public function __construct()
    {
        $this->access_token = env('JWT_ACCESS_KEY');
    }

    function index(Request $request)
    {
        $billCode = $request->get('bill', '');
        if (!$billCode) {
            return redirect()->route('payment.notify')->with("warning", "Đơn hàng thanh toán không xác định");
        }

        $order = PgwOrder::where('bill_code', $billCode)->first();
        if (!$order) {
            return redirect()->route('payment.notify')->with("warning", "Đơn hàng thanh toán không tồn tại");
        }

        if ($order->status == PgwOrder::STATUS_PAID) {
            return redirect()->route('payment.notify')->with("warning", "Đơn hàng đã được thanh toán thành công");
        }

        if ($order->status == PgwOrder::STATUS_FAIL) {
            return redirect()->route('payment.notify')->with("warning", "Đơn hàng đã thanh toán nhưng không thành công");
        }

        if ($order->status == PgwOrder::STATUS_REFUND) {
            return redirect()->route('payment.notify')->with("warning", "Đơn hàng thanh toán đã hoàn trả");
        }
        if ($order->status == PgwOrder::STATUS_CANCEL) {
            return redirect()->route('payment.notify')->with("warning", "Đơn hàng thanh toán đã bị huỷ");
        }
        if ($order->status == PgwOrder::STATUS_EXPIRED) {
            return redirect()->route('payment.notify')->with("warning", "Đơn hàng thanh toán đã hết hạn");
        }

        $partner = PgwPartner::where('code', $order->partner_code)->first();
        if (!$partner) {
            return redirect()->route('payment.notify')->with("warning", "Đơn hàng không được phép thanh toán");
        }

        if ($partner->status != PgwPartner::STATUS_ACTIVE) {
            return redirect()->route('payment.notify')->with("warning", "Đơn hàng chưa xác thực");
        }

        $merchantList = PgwPaymentMerchants::select(['pgw_payment_merchants.*', 'pgw_partner_resgistri_merchants.id as merchant_reg_id'])
            ->join('pgw_partner_resgistri_merchants', 'pgw_partner_resgistri_merchants.payment_merchant_id', '=', 'pgw_payment_merchants.id')
            ->where('status', PgwPaymentMerchants::STATUS_ACTIVE)
            ->where('pgw_partner_resgistri_merchants.partner_code', $partner->code)
            ->orderBy('sort', 'ASC')->get();
        if (!$merchantList) {
            return redirect()->route('payment.notify')->with("warning", "Không thể kết nối cổng thanh toán");
        }

        $bankList = PgwBanks::select(['pgw_banking_lists.*', 'pgw_partner_registri_bankings.id as bank_reg_id'])
            ->join('pgw_partner_registri_bankings', 'pgw_partner_registri_bankings.banking_list_id', '=', 'pgw_banking_lists.id')
            ->where('status', PgwBanks::STATUS_ACTIVE)
            ->where('type', PgwBanks::TYPE_BILLING)
            ->where('pgw_partner_registri_bankings.partner_code', $partner->code)
            ->orderBy('sort', 'ASC')->get();

        $contact = ContactLeadProcess::find($order->contact_lead_process_id);
        if (!$contact) {
            return redirect()->route('payment.notify')->with("warning", "Khách hàng không tồn tại");
        }

        $orderItems = PgwOrderDetail::where('order_id', $order->id)->get();
        if (!count($orderItems)) {
            return redirect()->route('payment.notify')->with("warning", "Sản phẩm thanh toán không hợp lệ");
        }

        /**
         * Update order status to processing
         */
        if ($order->status == PgwOrder::STATUS_NEW) {
            $endpoint = env('PGW_API', '') . "/api/v1/pgw-orders/" . $order->id;
            \App\Helper\Request::put($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                ],
                'json' => [
                    'status' => PgwOrder::STATUS_PROCESSING,
                ],
            ]);
        }

//        $merchantByGroup = [];
//        $group = [];
//        foreach ($merchantList as $key => $merchant) {
//            $group[] = $merchant;
//            if (($group && count($group) % 3 == 0) || $key == count($merchantList) - 1) {
//                $merchantByGroup[] = $group;
//                $group = [];
//            }
//        }
        $detailDiscount = !empty($order['discount_detail']) ? json_decode($order['discount_detail'],true) : [];
        $landingPageTracking = LandingPageTracking::where('landing_page_id', $order->landing_page_id)->first();
        return view('payment.index', [
            'order' => $order,
            'orderItems' => $orderItems,
            'contact' => $contact,
            'merchantList' => $merchantList,
            'bankList' => $bankList,
            'landingPageTracking' => $landingPageTracking,
            'detailDiscount' => $detailDiscount,
        ]);
    }

    function notify()
    {
        return view('payment.notify');
    }

    function pay(Request $request): JsonResponse
    {
        $orderId = $request->get('orderId', 0);
        $merchantId = $request->get('merchantId', 0);
        $bankRegId = $request->get('bankRegId', 0);

        $payGate = PayGate::init($orderId, $merchantId, $bankRegId);
        if (!$payGate) {
            throw new InvalidArgumentException('Cổng thanh toán không hoạt động');
        }
        $payUrl = $payGate->getPayUrl();
        if (!$payUrl) {
            throw new InvalidArgumentException('Cổng thanh toán không thể kết nối');
        }

        $requestMerchant = $payGate->getRequestMerchant();
        $merchant = $payGate->getMerchant();
        $bank = $payGate->getBank();
        $bankReg = $payGate->getBankRegister();
        $order = $payGate->getOrder();

        /**
         * Update order status to waiting
         */
        $endpoint = env('PGW_API', '') . "/api/v1/pgw-orders/" . $order->id;
        $data = [
            'merchant_code' => $merchant->code,
        ];
        if ($bank) {
            $data['banking_code'] = $bank->code;
        }
        if ($order->status != PgwOrder::STATUS_WAITING) {
            $data['status'] = PgwOrder::STATUS_WAITING;
        }
        \App\Helper\Request::put($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->access_token,
            ],
            'json' => $data,
        ]);

        $bankInfo = [];
        if ($bankReg) {
            $bankInfo = [
                'owner' => $bankReg->owner,
                'bank_number' => $bankReg->bank_number,
                'branch' => $bankReg->branch,
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => [
                'id' => $requestMerchant->id,
                'type' => $merchant->type,
                'amount' => number_format($order->amount, 0, ',', '.'),
                'payUrl' => $payUrl,
                'qrUrl' => $payGate->getQRUrl(),
                'vpcMerchTxnRef' => $requestMerchant->vpc_MerchTxnRef,
                'code' => $order->code,
                'bank' => $bankInfo
            ]
        ]);
    }

    function getBill(Request $request, $paymentMerchantCode)
    {
        $payGate = PayGate::initByPMC($paymentMerchantCode, $request->all());

        return $payGate->getBill();
    }

    function payBill(Request $request, $paymentMerchantCode)
    {
        $payGate = PayGate::initByPMC($paymentMerchantCode, $request->all());
        return $payGate->payBill();
    }

    function checkBill(Request $request): JsonResponse
    {
        $vpcMerchTxnRef = $request->get('code', '');
        $paymentRequestMerchant = PgwPaymentRequestMerchant::where('vpc_MerchTxnRef', $vpcMerchTxnRef)->first();
        if (!$paymentRequestMerchant) {
            throw new ResourceNotFoundException("Giao dịch không tồn tại");
        }

        $order = PgwOrder::find($paymentRequestMerchant->order_client_id);
        if (!$order) {
            throw new ResourceNotFoundException("Đơn hàng không tồn tại");
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => [
                'status' => $order->status,
                'return_url_true' => trim($order->return_url_true),
                'return_url_false' => trim($order->return_url_false),
            ]
        ]);
    }

    function result(Request $request, $paymentMerchantCode)
    {
        $payGate = PayGate::initByPMC($paymentMerchantCode, $request->all());

        $redirectUrl = json_decode($payGate->result(),true);

        if ($redirectUrl['returnUrl']) {
            return redirect($redirectUrl['returnUrl']);
        }
        if ($redirectUrl['returnCancelUrl']) {
            return redirect($redirectUrl['returnCancelUrl']);
        }
//        $requestMerchant = $payGate->getRequestMerchant();

        if (!empty($redirectUrl['success'])) {
            return redirect()->route('payment.notify')->with("success", "Đơn hàng của bạn đã được thanh toán thành công.");
        } else {
            return redirect()->route('payment.notify')->with("warning", "Thanh toán thất bại. Đơn hàng của bạn chưa được thanh toán");
        }
    }

    public function callback(Request $request)
    {
        $payGate = PayGate::initByPMC('transfermsb', $request->all());
        return $payGate->payBill();
    }

    public function changeStatusOrderCancel(Request $request)
    {
        try {
            $billCode = $request->bill;
            $order = PgwOrder::where('bill_code', $billCode)->first();
            if ($order->status == PgwOrder::STATUS_PAID) {
                throw new ResourceNotFoundException('Đơn hàng đã được thanh toán');
            }
            if ($order->status == PgwOrder::STATUS_FAIL) {
                throw new ResourceNotFoundException('Đơn hàng đã thanh toán thất bại.');
            }

            if ($order->status == PgwOrder::STATUS_REFUND) {
                throw new ResourceNotFoundException('Đơn hàng đã được hoàn trả');
            }

            if ($order->status == PgwOrder::STATUS_CANCEL) {
                throw new ResourceNotFoundException('Đơn hàng đã được huỷ');
            }

            if ($order->status == PgwOrder::STATUS_EXPIRED) {
                throw new ResourceNotFoundException('Đơn hàng đã hết hạn');
            }
            $endpoint = env('PGW_API', '') . "/api/v1/pgw-orders/" . $order->id;
            $data['status'] = PgwOrder::STATUS_CANCEL;
            $data['merchant_code'] = $order->merchant_code ?? null;
            $data['banking_code'] = $order->banking_code ?? null;
            \App\Helper\Request::put($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token,
                ],
                'json' => $data,
            ]);
            return response()->json([
                'data' => [
                    'message' => 'Cập nhật thành công',
                    'type' => 'success',
                    'url' => "/payment/pgw/?bill=" . $billCode
                ]
            ]);
        } catch (ResourceNotFoundException $ex) {
            return response()->json([
                'data' => [
                    'message' => $ex->getMessage(),
                    'type' => 'error',
                    'error_order'=> true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }

}
