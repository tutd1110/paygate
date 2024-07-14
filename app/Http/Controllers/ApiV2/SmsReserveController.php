<?php

namespace App\Http\Controllers\ApiV2;

use App\Helper\Utm;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactLeadRequest;
use App\Http\Requests\V2\SmsReserve\SmsReserveOnSendRequest;
use App\Models\ContactLead;
use App\Models\ContactLeadProcess;
use App\Models\Traffic;
use App\Repositories\Contact\ContactEloquentRepository;
use App\Repositories\Contact\ContactRepositoryInterface;
use Illuminate\Http\Request;

class SmsReserveController extends Controller
{
    private $mainModel;

    private $trafficModel;

    private $contactProcessModel;

    /***
     * @var ContactRepositoryInterface| ContactEloquentRepository
     */
    private $contactProcessRes;


    public function __construct(
        ContactLead $contactLead,
        Traffic $traffic,
        ContactLeadProcess $contactProcessModel
    ) {
        $this->mainModel = $contactLead;
        $this->trafficModel = $traffic;
        $this->contactProcessModel = $contactProcessModel;
        $this->contactProcessRes = app()->make(ContactRepositoryInterface::class);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(ContactLeadRequest $request)
    {

        $data = $request->validated();

        /***
         * Check landing page dạng payment
         */

        $data['utm_medium'] = $data['utm_medium'] ?? 'direct';
        $data['utm_source'] = $data['utm_source'] ?? 'direct';
        $data['utm_campaign'] = $data['utm_source'] ?? 'none';
        /***
         * Xử lý trường hợp bị không nhận được từ client các request thì cố gắng tìm trong traffic
         */
        if (!$data['utm_campaign'] == 'none') {
            $traffic = $this->trafficModel
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

        /***
         * Tạo contact
         */
        $contact = $this->mainModel->create($data);


        /***
         * tạo contact sau khi xử lý
         */
        $contactProcess = $this->contactProcessRes->process($contact);

        /****
         * trường hợp nếu là reserve contact thì thêm vào bảng reserve rồi xử lý dần về sau
         */
        $this->contactProcessRes->addReserveLogFromLanding($contactProcess, [
            'line' => $data['line'] ?? ''
        ]);

        return response()->json([
            'code' => 200,
            'message' => 'add contact reserve success',
            'data' => [
            ]
        ]);

    }


    public function onReceive(SmsReserveOnSendRequest $request)
    {

        /***
         * số điện thoại để gửi tin nhắn
         */
        $data = $request->validated();

        $couponCode = $data['coupon_code'];
        $event = $data['event'];
        $smsContent = $data['sms_content'] ?? '';
        $sendPhone = $data['send_phone'];

        $this->contactProcessRes->processOnReceiveSms($sendPhone, $event, $couponCode, $smsContent);

        return response()->json([
            'code' => 200,
            'message' => 'add contact reserve success',
            'data' => [
            ]
        ]);
    }
}
