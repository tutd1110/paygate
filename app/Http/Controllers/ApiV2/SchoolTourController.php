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

class SchoolTourController extends Controller
{
    const CODE_VERIFIED_TEMPLATE_SEND_SMS = 'contact_verification_sms';
    const DATE_EXPIRED_SCHOOL_TOUR = 14;

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
        $this->urlCheckVerifyLdp = env('URL_SCHOOL_TOUR').'/api_server/thirdparty/schooltour/checkInfo?apikey='.env('API_KEY_SCHOOL_TOUR');
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
        if (!empty($checkVerifyLdp['isVerify'])) {
            $verifiy_type = ContactLeadProcess::VERIFIY_AUTO;
        }

        if (!empty($contactLeadProcess->landingPage->verifiy_type)) {
            if ($verifiy_type == ContactLeadProcess::VERIFIY_ZNS) {
                $contactLeadProcess['user_id'] = $checkVerifyLdp['idOriginal'];
                $contactLeadProcess['code'] = $this->getCodeActive($checkVerifyLdp);
                $contactLeadProcess['date_expired'] = self::DATE_EXPIRED_SCHOOL_TOUR;
                PushZnsHocMai::dispatch($contactLeadProcess, 'SchoolTour');
            } elseif ($verifiy_type == ContactLeadProcess::VERIFIY_SMS) {
                $this->sendSms($contactLeadProcess,$checkVerifyLdp['urlVerify']);
            } elseif ($verifiy_type == ContactLeadProcess::VERIFIY_AUTO) {
                $contactLeadProcess->verified = 'yes';
                $contactLeadProcess->save();
                PushContactWithZnsToCrm::dispatch($contactLeadProcess, ['status' => PushContactStatus::CREATE_BILL]);
            } elseif($verifiy_type == ContactLeadProcess::VERIFIY_EMAIL){
                $this->sendMail($contactLeadProcess,$checkVerifyLdp['urlVerify']);
            }else{

            }
        }


        if ($contact->action == 'reserve') {
            PushReserveContact::dispatch($contact, [
                'session_id' => $request->input('session_id'),
                'uri' => $request->input('uri'),
                'fsuid' => $request->input('fsuid'),
            ]);
        }
        if (!empty($checkVerifyLdp['isVerify'])) {
            return response()->json([
                'message' => 'save success',
                'data' => [
                    'urlVerify'=>$checkVerifyLdp['urlVerify']
                ]
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

    public function checkVerifyLdp($contactLeadProcess = null, $data)
    {
        try {
            if (empty($contactLeadProcess)) {
                return false;
            }
            $checkVerifyLdp = \App\Helper\Request::post($this->urlCheckVerifyLdp, [
                'headers' => [
                    'Content-Type: application/json'
                ],
                'json' => [
                    'phone' => $contactLeadProcess->phone,
                    'name' => $contactLeadProcess->full_name,
                    "idSchool" => $data->idSchool ?? 1,
                    "typeLDP" => $data->typeLDP ?? 0,
                    "idLDP" => $contactLeadProcess->landing_page_id,
                    "agentCode" => $data['agentCode'],
                ]
            ]);
            $checkVerifyLdp = (json_decode((string)$checkVerifyLdp->getBody(), true))['data'] ?? false;
            if (empty($checkVerifyLdp)) {
                return false;
            }
            return $checkVerifyLdp;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }


    public function sendMail($contactLeadProcess,$link){

        $customUrl = Str::random('6') . $contactLeadProcess['id'];
        $link_active = ShortLink::handler($link, $customUrl);

        $emailTemplateCode = 'school_tour';
        $templateEmail = EmailTemplates::where('code',$emailTemplateCode)->first();

        $emailContent = str_replace('{full_name}', $contactLeadProcess->full_name, htmlspecialchars_decode($templateEmail->content));
        $emailContent = str_replace('{link_active}', $link_active, $emailContent);
        $emailContent = str_replace('{date_expired}', 15, $emailContent);
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

    public function sendSms($contactLeadProcess,$link)
    {
        try {
            $smsContent = "Xin chào bạn {full_name} Để hoàn tất quá trình đăng ký dùng thử phòng luyện thi ảo Easy IELTS Online Assessment,bạn vui lòng truy cập vào link:{link_active} .Link xác thực có thời hạn {date_expired} ngày.";
            $landingPage = LandingPage::find($contactLeadProcess['landing_page_id']);
            if ($landingPage) {
                $customUrl = Str::random('6') . $contactLeadProcess['id'];
                $link_active = ShortLink::handler($link, $customUrl);
                $template = MessageTemplate::where('code', self::CODE_VERIFIED_TEMPLATE_SEND_SMS)->where('landing_page_id', $landingPage['id'])->first();
                if ($template->content ?? '') {
                    $smsContent = $template->content;
                }
            }

            $smsContent = str_replace('{full_name}', $contactLeadProcess['full_name'], $smsContent);
            $smsContent = str_replace('{link_active}', $link_active, $smsContent);
            $smsContent = str_replace('{date_expired}', 15, $smsContent);
            if (!empty($landingPage) && !empty($contactLeadProcess)) {
                $sendSms = SendSms::send($contactLeadProcess['phone'], $smsContent, $contactLeadProcess['id'], get_class((object)$contactLeadProcess), $contactLeadProcess['id']);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    private function getCodeActive($checkVerifyLdp){
        $parts = parse_url($checkVerifyLdp['urlVerify']);
        parse_str($parts['query'], $query);
        if(!empty($query['apikey'])) unset($query['apikey']);
        $code = 'xacthuc-taikhoan?'.http_build_query($query);
        return $code;
    }
}
