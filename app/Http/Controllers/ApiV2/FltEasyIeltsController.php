<?php

namespace App\Http\Controllers\ApiV2;

use App\Helper\SendSms;
use App\Helper\ShortLink;
use App\Helper\Utm;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactLeadRequest;
use App\Jobs\PushContact\PushContactToCrm;
use App\Jobs\PushContact\PushContactWithZnsToCrm;
use App\Jobs\PushContact\PushReserveContact;
use App\Jobs\PushZnsHocMai;
use App\Jobs\SendEmailHocMai;
use App\Lib\PushContactStatus;
use App\Models\ContactLead;
use App\Models\ContactLeadProcess;
use App\Models\EmailSave;
use App\Models\EmailTemplates;
use App\Models\LandingPage;
use App\Models\MessageTemplate;
use App\Models\Traffic;
use App\Repositories\Contact\ContactEloquentRepository;
use App\Repositories\Contact\ContactRepositoryInterface;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class FltEasyIeltsController extends Controller
{
    const CODE_VERIFIED_TEMPLATE_SEND_SMS = 'contact_verification_sms';
    const LINK_ACTIVE_EASY_IELTS = 'https://assessment.icanconnect.vn/';
    const LINK_EXPIRED_EASY_IELTS = 'https://www.easyielts.com.vn/oa-phong-luyen-ao?utm_source=em';
    const CODE_EXPIRED_EASY_IELTS = 'verify_easy_ielts_online_assessment_expired';
    const CODE_ACTIVE_EASY_IELTS = 'verify_easy_ielts_online_assessment';

    private $mainModel;

    private $trafficModel;

    private $contactProcessModel;

    /***
     * @var ContactEloquentRepository  | ContactRepositoryInterface
     */
    private $contactEloquentRepository;


    public function __construct(ContactLead $contactLead, Traffic $traffic, ContactLeadProcess $contactProcessModel, ContactRepositoryInterface $contactEloquentRepository)
    {
        $this->mainModel = $contactLead;
        $this->trafficModel = $traffic;
        $this->contactProcessModel = $contactProcessModel;
        $this->contactEloquentRepository = $contactEloquentRepository;
        $this->urlCheckVerifyLdp = env('URL_FLT_EASY_IELTS') . '/api/v1/thirdparty/register-train-product';
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
        if (empty($data['utm_medium'])) {
            $data['utm_medium'] = 'direct';
        }

        if (empty($data['utm_source'])) {
            $data['utm_source'] = 'direct';
        }

        if (empty($data['utm_campaign'])) {
            $data['utm_campaign'] = 'none';
        }
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


        $contact = $this->mainModel->create($data);
        $contactLeadProcess = $this->contactEloquentRepository->process($contact);
        PushContactToCrm::dispatch($contact);

        $checkVerifyLdp = $this->checkVerifyLdp($contactLeadProcess, $request->all());
        /***
         * Kiểm tra landingPage nếu có verifiy_type thì gửi tin nhắn xác thực khách hàng
         */
        $verifiy_type = $contactLeadProcess->landingPage->verifiy_type ?? null;
        if (!empty($checkVerifyLdp['joined'])) {
            $verifiy_type = ContactLeadProcess::VERIFIY_AUTO;
        }

        if (!empty($contactLeadProcess->landingPage->verifiy_type)) {
            if ($verifiy_type == ContactLeadProcess::VERIFIY_ZNS) {
                $contactLeadProcess['user_id'] = $checkVerifyLdp['studentId'];
                PushZnsHocMai::dispatch($contactLeadProcess, 'EasyIELTS');
                $this->sendMail($contactLeadProcess, self::LINK_ACTIVE_EASY_IELTS);
            } elseif ($verifiy_type == ContactLeadProcess::VERIFIY_AUTO) {
                $contactLeadProcess->verified = 'yes';
                $contactLeadProcess->save();
                PushContactWithZnsToCrm::dispatch($contactLeadProcess, ['status' => PushContactStatus::CREATE_BILL]);
                $this->sendMail($contactLeadProcess, self::LINK_EXPIRED_EASY_IELTS);
            } else {

            }
        }


        if ($contact->action == 'reserve') {
            PushReserveContact::dispatch($contact, [
                'session_id' => $request->input('session_id'),
                'uri' => $request->input('uri'),
                'fsuid' => $request->input('fsuid'),
            ]);
        }


        return response()->json([
            'message' => 'save success',
            'data' => [
                'contact' => $contact,
                'contactLeadProcess' => $contactLeadProcess,
//                'link_active' => $link
            ]
        ]);

    }

    public function checkVerifyLdp($contactLeadProcess = null, $data = null)
    {
        try {
            if (empty($contactLeadProcess)) {
                return false;
            }
            $checkVerifyLdp = \App\Helper\Request::post($this->urlCheckVerifyLdp, [
                'headers' => [
                    'Content-Type: application/json',
                    'x-api-key' => env('API_KEY_FLT_EASY_IELTS')
                ],
                'json' => [
                    'email' => $contactLeadProcess->email,
                ]
            ]);

            $checkVerifyLdp = (json_decode((string)$checkVerifyLdp->getBody(), true))['metadata'] ?? false;
            if (empty($checkVerifyLdp)) {
                throw new \Exception('Chưa kiểm tra được tài khoản!');
            }
            return $checkVerifyLdp;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }


    public function sendMail($contactLeadProcess, $link)
    {
        $emailTemplateCode = ($link == self::LINK_ACTIVE_EASY_IELTS) ? (self::CODE_ACTIVE_EASY_IELTS) : self::CODE_EXPIRED_EASY_IELTS;
        $templateEmail = EmailTemplates::where('code', $emailTemplateCode)->first();

        $emailContent = str_replace('{full_name}', $contactLeadProcess->full_name, htmlspecialchars_decode($templateEmail->content));
        $emailContent = str_replace('{email}', $contactLeadProcess->email, $emailContent);
        $emailContent = str_replace('{link_active}', $link, $emailContent);
        $sendEmailParams = [
            'landing_page_id' => $contactLeadProcess['landing_page_id'],
            'contact_lead_process_id' => $contactLeadProcess['id'],
            'from_email' => 'noreply@hocmai.vn',
            'from_name' => 'Hệ thống giáo dục HOCMAI',
            'to_email' => $contactLeadProcess['email'],
            'to_name' => $contactLeadProcess['full_name'],
            'subject' => $templateEmail->subject,
            'content' => $emailContent,
            'file_attach' => 'nullable|string',
        ];
        $email = EmailSave::create($sendEmailParams);
        SendEmailHocMai::dispatch($email);
    }
}
