<?php

namespace App\Http\Controllers\Api\PGW;

use App\Exceptions\InvalidArgumentException;
use App\Exceptions\ResourceNotFoundException;
use App\Helper\Utm;
use App\Http\Controllers\Controller;
use App\Http\Requests\PGW\PgwOrderRequest;
use App\Models\ContactLead;
use App\Models\ContactLeadProcess;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPartnerRegistriBanking;
use App\Models\PGW\PgwPartnerResgistriMerchant;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\Traffic;
use App\Payment\PayGate;
use App\Repositories\Contact\ContactEloquentRepository;
use App\Repositories\Contact\ContactPushEloquentRepository;
use App\Repositories\Contact\ContactPushRepositoryInterface;
use App\Repositories\Contact\ContactRepositoryInterface;
use App\Repositories\PGW\PgwOrderRepository;
use App\Repositories\PGW\PgwOrderInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class PgwOrderController extends Controller
{
    /***
     * @var PgwOrderInterface | PgwOrderRepository
     */
    private $pgwOrderRes;
    /***
     * @var ContactEloquentRepository  | ContactRepositoryInterface
     */
    private $contactEloquentRepository;
    /***
     * @var ContactPushRepositoryInterface | ContactPushEloquentRepository
     */
    private $pushContactRes;

    public function __construct(PgwOrderInterface $pgwOrderRes, ContactRepositoryInterface $contactEloquentRepository)
    {
        $this->pgwOrderRes = $pgwOrderRes;
        $this->contactEloquentRepository = $contactEloquentRepository;
        $this->pushContactRes = app()->make(ContactPushRepositoryInterface::class);
    }

    public function index(Request $request)
    {
        $params = $request->all();
        $orders = $this->pgwOrderRes->getOrders($params);
        return response()->json([
            'message' => 'success',
            'data' => [
                'orders' => $orders->toArray()
            ]
        ]);

    }

    public function store(PgwOrderRequest $request)
    {
//        dd($request->all());
        $isApi = $request->is_api;
        $data = $request->validated();
        $data['partner_code'] = strtoupper($data['partner_code']);

        /***
         * Check landing page dạng payment
         */

        $data['utm_medium'] = $data['utm_medium'] ?? 'direct';
        $data['utm_source'] = $data['utm_source'] ?? 'direct';
        $data['utm_campaign'] = $data['utm_campaign'] ?? 'none';
        $data['status'] = 'new';
        try {
            /***
             * Xử lý trường hợp bị không nhận được từ client các request thì cố gắng tìm trong traffic
             */
            if (!$data['utm_campaign'] == 'none') {
                $traffic = Traffic::query()
                    ->where('session_id', $data['session_id'])
                    ->where('landing_page_id', $data['landing_page_id'])
                    ->first();

                if ($traffic) {
                    $data['utm_campaign'] = $traffic->utm_campaign;
                    $data['utm_medium'] = $traffic->utm_medium;
                    $data['utm_source'] = $traffic->utm_source;
                }
            }

            $utmCampaign = Utm::campaign($data['utm_campaign'] ?? '');
            $utmContent = Utm::content($data['utm_content'] ?? '');
            $utmCreator = Utm::creator($data['utm_creator'] ?? '');
            $utmMedium = Utm::medium($data['utm_medium'] ?? '');
            $utmSource = Utm::source($data['utm_source'] ?? '');
            $utmTerm = Utm::term($data['utm_term'] ?? '');

            $utmProcessArray = [
                'utm_campaign_id' => $utmCampaign->id ?? 0,
                'utm_content_id' => $utmContent->id ?? 0,
                'utm_creator_id' => $utmCreator->id ?? 0,
                'utm_medium_id' => $utmMedium->id ?? 0,
                'utm_source_id' => $utmSource->id ?? 0,
                'utm_term_id' => $utmTerm->id ?? 0,
            ];

            $data = array_merge($data, $utmProcessArray);

            $contact = ContactLead::create($data);

            $contactProcess = $this->contactEloquentRepository->process($contact);

            $data['contact_lead_process_id'] = $contactProcess->id ?? 0;
            $pgwOrder = $this->pgwOrderRes->create($data);

            /****
             * trường hợp nếu là reserve contact thì thêm vào bảng contact_lead_process_reserve_logs
             */
            $landingPage = $contactProcess->landingPage;
            if ($landingPage->purpose == 'reserve') {
                $arrPushResever['line'] = $data['line'] ?? '';
                $arrPushResever['landing_page_id'] = $data['landing_page_id'] ?? null;
                if ($landingPage->push_crm_invoice_delay == 0) {
                    $arrPushResever['is_crm_pushed'] = 1;
                    $arrPushResever['crm_pushed_at'] = date('Y-m-d H:i:s');
                }
                $this->contactEloquentRepository->addReserveLogFromLanding($contactProcess, $arrPushResever);
            }
            if ($request->merchant_code) {
                if ($request->merchant_code != 'transfer') {
                    $redirectUrl = config('hocmai.PAYGATE_URL') . $pgwOrder->bill_code . '&merchant_code=' . $request->merchant_code;
                } else {
                    $redirectUrl = config('hocmai.PAYGATE_URL') . $pgwOrder->bill_code . '&merchant_code=' . $request->banking_code;
                }
            } else {
                $redirectUrl = config('hocmai.PAYGATE_URL') . $pgwOrder->bill_code;
            }

            if ($isApi ?? false) {
                return response()->json([
                    'code' => 200,
                    'message' => 'success',
                    'data' => [
                        'pgwOrder' => $pgwOrder,
                        'redirect_url' => $redirectUrl,
                    ]
                ]);
            } else {
                return redirect($redirectUrl);
            }
        } catch (Exception $ex) {
            return response()->json([
                'code' => 400,
                'message' => 'Bad request, somethings went wrong',
                'error' => $ex->getMessage()
            ]);
        }
    }


    public function updateOrderPaid(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return $validator->getMessageBag();
            }
            if (empty($request->id)) {
                return response()->json([
                    'code' => 200,
                    'message' => 'update success',
                    'data' => [
                        'checkUpdateOrderPaid' => false,
                    ]
                ]);
            }
            $orders = PgwOrder::query()->with('paymentMerchant')->with('contact')->with('banking')->where('id', $request->id)->first();
            $merchantId = !empty($orders['merchant_code']) ? $orders['paymentMerchant']['id'] : null;;
            $bankId = !empty($orders['banking_code']) ? $orders['banking']['id'] : 0;
            if (!empty($merchantId)) {
                $merchantRegId = PgwPartnerResgistriMerchant::query()
                    ->select('id')
                    ->where('partner_code', $orders['partner_code'])
                    ->where('payment_merchant_id', $merchantId)
                    ->first();
            }
            if (!empty($bankId)) {
                $bankRegId = PgwPartnerRegistriBanking::query()->select('id')
                    ->where('partner_code', $orders['partner_code'])
                    ->where('banking_list_id', $bankId)
                    ->first();
            }
            $merchantRegId = $merchantRegId['id'] ?? 0;
            $bankRegId = $bankRegId['id'] ?? 0;
            $orderId = $orders['id'];
            $payGate = PayGate::init($orderId, $merchantRegId, $bankRegId);
            $updateOrder = $this->pgwOrderRes->updateOrderPaid($orders, $payGate);
            return response()->json([
                'code' => 200,
                'message' => 'update success',
                'data' => $updateOrder
            ]);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'code' => 200,
                'message' => 'update fail',
                'data' => [
                    'checkUpdateOrderPaid' => false,
                    'message' => $exception->getMessage()
                ]
            ]);
        } catch (ResourceNotFoundException $exception) {
            return response()->json([
                'code' => 200,
                'message' => 'update fail',
                'data' => [
                    'checkUpdateOrderPaid' => false,
                    'message' => $exception->getMessage()
                ]
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'code' => 200,
                'message' => 'update fail',
                'data' => [
                    'checkUpdateOrderPaid' => false,
                    'message' => $exception->getMessage()
                ]
            ]);
        }
    }

    public function updateStatusClient(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return $validator->getMessageBag();
            }
            $checkUpdate = $this->pgwOrderRes->updateStatusClient($request->id);
            return response()->json([
                'code' => 200,
                'data' => [
                    'status' => $checkUpdate['status'],
                    'message' => $checkUpdate['message']
                ]
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'code' => 500,
                'message' => 'update fail',
                'data' => [
                    'status' => false,
                    'message' => $exception->getMessage()
                ]
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, PgwOrderRequest $request)
    {
        $data = $request->all();
        if (isset($data['partner_code'])) {
            $data['partner_code'] = strtoupper($data['partner_code']);
        }

        $order = PgwOrder::find($id);
        if (empty($order)) {
            return response()->json([
                'code' => 404,
                'message' => 'Order Id không tồn tại.',
            ], 404);
        }
        /*
        if (($data['code'] ?? '') != $order->code) {
            return response()->json([
                'code' => 422,
                'message' => 'code not true',
                'data' => [
                ]
            ], 422);
        }
        $user_id = $data['user_id'] ?? 0;
        $contactProcess = ContactLeadProcess::where('user_id', $user_id)
            ->where('landing_page_id', $data['landing_page_id'])->orderBy('id', 'desc')->first();
        $data['contact_lead_process_id'] = $contactProcess->id ?? 0;

        if (!$contactProcess) {
            return response()->json([
                'code' => 404,
                'message' => 'contact not found!',
            ], 404);
        }
        */

        try {
            $order = $this->pgwOrderRes->update($id, $data);

            return response()->json([
                'code' => 200,
                'message' => 'update success',
                'data' => [
                    'order' => $order,
                ]
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'code' => 400,
                'message' => 'Update fail, somethings went wrong',
                'error' => $ex->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function checkActiveCode(Request $request)
    {
        $active_code = trim($request->input('active_code'));
        $partner_code = "";
        $code = "";
        if (!empty($active_code)) {
            $code_str = substr($active_code, -7);
            $code = str_replace("-", "", $code_str);
            $partner_code = str_replace($code_str, "", $active_code);
        }

        $orderModel = new PgwOrder();
        $getOrderPaid = $orderModel->checkActiveCodePaid($partner_code, $code);
        if ($getOrderPaid) {
            $getOrderPaid->orderDetail->toArray();
            return response()->json([
                'status' => 'success',
                'message' => 'Thông tin đơn hàng',
                'data' => $getOrderPaid,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tồn tại mã kích hoạt',
                'data' => []
            ]);
        }

    }

    public function getOrderByBillCode(Request $request)
    {
        $bill_code = trim($request->input('bill_code'));
        if (!$bill_code) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi chưa truyền param bill_code.',
            ]);
        }

        $orderModel = new PgwOrder();
        $getOrder = $orderModel->getOrderByBillcode($bill_code);
        if ($getOrder) {
            $getOrder->orderDetail->toArray();
            $contact = ContactLeadProcess::find($getOrder->contact_lead_process_id);
            $data_contact = [
                'name' => $contact->full_name,
                'phone' => $contact->phone,
                'address' => $contact->address,
                'email' => $contact->email
            ];
            return response()->json([
                'status' => 'success',
                'message' => 'Thông tin đơn hàng',
                'data' => [
                    'contact' => $data_contact,
                    'orders' => $getOrder
                ],
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tồn tại mã bill trên hệ thống',
                'data' => []
            ]);
        }
    }

    /** Hàm callback sau khi tạo đơn hàng thành công */
    public function callback(Request $request)
    {
        try {
            $pgwPaymentRequest = $request->all();
            $call_back = $this->pgwOrderRes->callback($pgwPaymentRequest);
            if (!empty($call_back['status'])){
                return response()->json([
                    'code' => 200,
                    'message' => 'success',
                    'data'=> [
                        'status' => $call_back['status'],
                        'message' => $call_back['message']
                    ]
                ]);
            }else{
                return response()->json([
                    'code' => 200,
                    'message' => 'fail',
                    'data'=> [
                        'message' => 'Đơn hàng đã được cập nhật trên hệ thống'
                    ]
                ]);
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
