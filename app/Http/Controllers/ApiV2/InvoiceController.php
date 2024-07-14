<?php

namespace App\Http\Controllers\ApiV2;

use App\Helper\SendSms;
use App\Helper\Utm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\InvoiceList;
use App\Http\Requests\V2\Invoice\InvoiceRequest;
use App\Http\Requests\V2\Invoice\UpdateInvoiceRequest;
use App\Jobs\SendSmsPaymentCode;
use App\Lib\PushContactStatus;
use App\Models\ContactLead;
use App\Models\Invoice\Invoice;
use App\Models\Traffic;
use App\Repositories\Contact\ContactEloquentRepository;
use App\Repositories\Contact\ContactPushEloquentRepository;
use App\Repositories\Contact\ContactPushRepositoryInterface;
use App\Repositories\Contact\ContactRepositoryInterface;
use App\Repositories\Invoice\InvoiceInterface;
use App\Repositories\Invoice\InvoiceRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use League\CommonMark\Parser\Block\ParagraphParser;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    /***
     * @var InvoiceInterface | InvoiceRepository
     */
    private $invoiceRes;

    /***
     * @var ContactEloquentRepository  | ContactRepositoryInterface
     */
    private $contactEloquentRepository;


    /***
     * @var ContactPushRepositoryInterface | ContactPushEloquentRepository
     */
    private $pushContactRes;

    public function __construct(InvoiceInterface $invoiceRes, ContactRepositoryInterface $contactEloquentRepository)
    {

        $this->invoiceRes = $invoiceRes;
        $this->contactEloquentRepository = $contactEloquentRepository;
        $this->pushContactRes = app()->make(ContactPushRepositoryInterface::class);
    }

    public function index(InvoiceList $request)
    {
        $query = Invoice::query();

        $filter = $request->all();


        $query->with('contact');

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

        if (isset($filter['code'])) {
            if (is_array($filter['code'])) {
                $query = $query->whereIn('code', $filter['code']);
            } else {
                $query = $query->where('code', $filter['code']);
            }
        }

        if (isset($filter['user_id'])) {
            if (is_array($filter['user_id'])) {
                $query = $query->whereIn('user_id', $filter['user_id']);
            } else {
                $query = $query->where('user_id', $filter['user_id']);
            }
        }

        if (isset($filter['contact_lead_process_id'])) {
            if (is_array($filter['contact_lead_process_id'])) {
                $query = $query->whereIn('contact_lead_process_id', $filter['contact_lead_process_id']);
            } else {
                $query = $query->where('contact_lead_process_id', $filter['contact_lead_process_id']);
            }
        }

        if (isset($filter['status'])) {
            if (is_array($filter['status'])) {
                $query = $query->whereIn('status', $filter['status']);
            } else {
                $query = $query->where('status', $filter['status']);
            }
        }


        if (isset($filter['order'])) {
            if (is_array($filter['order'])) {
                foreach ($filter['order'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            } else {
                $query = $query->orderBy($filter['order'], $filter['direction'] ?? 'asc');
            }
        }

        if (isset($filter['start_date'])) {
            $query = $query->where('created_at', '>=',
                Carbon::createFromFormat('Y-m-d', $filter['start_date'])->startOfDay()->format('Y-m-d H:i:s'));
        }

        if (isset($filter['end_date'])) {
            $query = $query->where('created_at', '<=',
                Carbon::createFromFormat('Y-m-d', $filter['end_date'])->endOfDay()->format('Y-m-d H:i:s'));
        }

        if ($request->get('count')) {
            $invoices = $query->paginate($request->get('limit', 20));
        } else {
            $invoices = $query->simplePaginate($request->get('limit', 20));
        }

        return response()->json([
            'message' => 'get success',
            'data' => [
                'invoices' => $invoices->setCollection($invoices->getCollection()->makeVisible('created_at'))
            ]
        ]);
    }

    /***
     * @param InvoiceRequest $request
     *
     *                               Tạo mới đơn hàng trạng thái new
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector | JsonResponse
     * @throws \Exceptiont
     */
    public function store(InvoiceRequest $request)
    {
        $testMode = $request->is_test ?? false;


        $data = $request->validated();

        /***
         * Check landing page dạng payment
         */

        $data['utm_medium'] = $data['utm_medium'] ?? 'direct';
        $data['utm_source'] = $data['utm_source'] ?? 'direct';
        $data['utm_campaign'] = $data['utm_campaign'] ?? 'none';
        $data['status'] = 'new';

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


        /***
         * tạo contact sau khi xử lý
         */
        $contactProcess = $this->contactEloquentRepository->process($contact);

        $data['contact_lead_process_id'] = $contactProcess->id ?? 0;

        /***
         * Tiếp tục tạo đơn hàng sau khi tạo contact
         */
        $invoice = $this->invoiceRes->create($data);
        if ($testMode) {
            $redirectUrl = 'https://hocmai.vn/payment2/quickpay/index2.php?bill='.$invoice->code;
        } else {
            $redirectUrl = config('hocmai.hocmai_url').config('hocmai.HOCMAI_PAYMENT_PATH').$invoice->code;
        }


        if ($data['is_api'] ?? false) {
            return response()->json([
                'code' => 200,
                'message' => 'get success',
                'data' => [
                    'invoice' => $invoice,
                    'redirect_url' => $redirectUrl,
                ]
            ]);
        } else {
            return redirect($redirectUrl);
        }



    }

    public function show($code)
    {
        $invoice = Invoice::with(['items', 'contact'])->where('code', $code)->first();

        if ($invoice) {
            return response()->json([
                'code' => 200,
                'message' => 'get success',
                'data' => [
                    'invoice' => $invoice,
                ]
            ]);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'invoice not found',
                'data' => [
                    'invoice' => $invoice,
                ]
            ], Response::HTTP_NOT_FOUND);
        }


    }

    public function update($code, UpdateInvoiceRequest $invoiceRequest)
    {
        $invoice = Invoice::with('items')->where('code', $code)->first();


        if ($invoice) {
            $data = $invoiceRequest->validated();

            if (isset($data['is_active_code_used']) && ($data['is_active_code_used'])
                && (!$invoice->is_used_active_code)) {
                $data['active_code_used_at'] = Carbon::now();
            }

            $invoice->fill($data);
            $invoice->save();

            if ( ($data['status'] ?? '') == 'paid') {
                /****
                 * Đã thanh toán thì push
                 */
                try {
                    $this->pushContactRes->pushContactLeadProcess($invoice->contact, [
                        'status' => PushContactStatus::PAYMENT_BILL,
                        'line' => $invoice->line ?? '',
                    ]);
                    $invoice->is_crm_pushed = 1;
                    $invoice->save();
                } catch (\Exception $exception) {
                    Log::error($exception);
                }


                SendSmsPaymentCode::dispatch($invoice);
            }

            return response()->json([
                'code' => 200,
                'message' => 'update success',
                'data' => [
                    'invoice' => $invoice,
                ]
            ]);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'invoice not found',
                'data' => [
                ]
            ], Response::HTTP_NOT_FOUND);
        }

    }


}
