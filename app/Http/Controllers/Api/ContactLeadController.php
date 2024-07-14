<?php

namespace App\Http\Controllers\Api;

use App\Helper\SendSms;
use App\Helper\ShortLink;
use App\Helper\Utm;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactLeadRequest;
use App\Http\Requests\ListContactLeadRequest;
use App\Jobs\ProcessContactJob;
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
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ContactLeadController extends Controller
{
    const CODE_VERIFIED_TEMPLATE_SEND_SMS = 'contact_verification_sms';

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
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(ListContactLeadRequest $request)
    {
        $query = $this->contactProcessModel;

        $filter = $request->all();

        if (isset($filter['landing_page_id'])) {
            if (is_array($filter['landing_page_id'])) {
                $query = $query->whereIn('landing_page_id', $filter['landing_page_id']);
            } else {
                $query = $query->where('landing_page_id', $filter['landing_page_id']);
            }
        }


        $queryListUtmSource = clone $query;
        $queryListUtmCampaign = clone $query;

        if (isset($filter['id'])) {
            if (is_array($filter['id'])) {
                $query = $query->whereIn('id', $filter['id']);
            } else {
                $query = $query->where('id', $filter['id']);
            }
        }

        if (isset($filter['user_id'])) {
            if (is_array($filter['user_id'])) {
                $query = $query->whereIn('user_id', $filter['user_id']);
            } else {
                $query = $query->where('user_id', $filter['user_id']);
            }
        }


        if (isset($filter['campaign_id'])) {
            if (is_array($filter['campaign_id'])) {
                $query = $query->whereIn('campaign_id', $filter['campaign_id']);
            } else {
                $query = $query->where('campaign_id', $filter['campaign_id']);
            }
        }

        if (isset($filter['crm_id'])) {
            if (is_array($filter['crm_id'])) {
                $query = $query->whereIn('crm_id', $filter['crm_id']);
            } else {
                $query = $query->where('crm_id', $filter['crm_id']);
            }
        }

        if (isset($filter['olm_id'])) {
            if (is_array($filter['olm_id'])) {
                $query = $query->whereIn('olm_id', $filter['olm_id']);
            } else {
                $query = $query->where('olm_id', $filter['olm_id']);
            }
        }

        if (isset($filter['sashi_id'])) {
            if (is_array($filter['sashi_id'])) {
                $query = $query->whereIn('sashi_id', $filter['sashi_id']);
            } else {
                $query = $query->where('sashi_id', $filter['sashi_id']);
            }
        }

        if (isset($filter['fullname'])) {
            if (is_array($filter['fullname'])) {
                $query = $query->whereIn('fullname', 'like', $filter['fullname'] . '%');
            } else {
                $query = $query->where('fullname', 'like', $filter['fullname'] . '%');
            }
        }

        if (isset($filter['phone'])) {
            if (is_array($filter['phone'])) {
                $query = $query->whereIn('phone', $filter['phone']);
            } else {
                $query = $query->where('phone', $filter['phone']);
            }
        }

        if (isset($filter['email'])) {
            if (is_array($filter['email'])) {
                $query = $query->whereIn('email', $filter['email']);
            } else {
                $query = $query->where('email', $filter['email']);
            }
        }

        if (isset($filter['class'])) {
            if (is_array($filter['class'])) {
                $query = $query->whereIn('class', $filter['class']);
            } else {
                $query = $query->where('class', $filter['class']);
            }
        }

        if (isset($filter['register_ip'])) {
            if (is_array($filter['register_ip'])) {
                $query = $query->whereIn('register_ip', $filter['register_ip']);
            } else {
                $query = $query->where('register_ip', $filter['register_ip']);
            }
        }
        if (isset($filter['verified'])) {
            $query = $query->where('verified', $filter['verified']);
        }

        if (isset($filter['start_date'])) {
            $query = $query->where('created_at', '>=',
                Carbon::createFromFormat('Y-m-d', $filter['start_date'])->startOfDay()->format('Y-m-d H:i:s'));
        }

        if (isset($filter['end_date'])) {
            $query = $query->where('created_at', '<=', Carbon::createFromFormat('Y-m-d', $filter['end_date'])->endOfDay()->format('Y-m-d H:i:s'));
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
        if ($request->get('export') == true) {
            $listContacts = $query->get();
        } elseif ($request->get('count')) {
            $listContacts = $query->paginate($request->get('limit', 20));
        } else {
            $listContacts = $query->simplePaginate($request->get('limit', 20));
        }

        $data = [
            'contacts' => $listContacts,
        ];


        if (in_array('utm_campaigns', $request->get('append_data', []))) {
            $listCampaign = $queryListUtmCampaign->select('utm_campaign')->groupBy('utm_campaign')->get();
            $data['utm_campaigns'] = $listCampaign;
        }

        if (in_array('utm_sources', $request->get('append_data', []))) {
            $listUtmSource = $queryListUtmSource->select('utm_source')->groupBy('utm_source')->get();
            $data['utm_sources'] = $listUtmSource;
        }

        return response()->json([
            'message' => 'get success',
            'data' => $data,
        ]);
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

        /***
         * Kiểm tra landingPage nếu có verifiy_type thì gửi tin nhắn xác thực khách hàng
         */
        $verifiy_type = $contactLeadProcess->landingPage->verifiy_type ?? null;
        if (!empty($contactLeadProcess->landingPage->verifiy_type)) {
            if ($verifiy_type == ContactLeadProcess::VERIFIY_ZNS) {
                PushZnsHocMai::dispatch($contactLeadProcess);
            }
            elseif ($verifiy_type == ContactLeadProcess::VERIFIY_EMAIL) {
                $this->sendMail($contactLeadProcess);
            }
            elseif ($verifiy_type == ContactLeadProcess::VERIFIY_SMS) {
                $this->sendSms($contactLeadProcess);
            }
            elseif ($verifiy_type == ContactLeadProcess::VERIFIY_AUTO) {
                $contactLeadProcess->verified = 'yes';
                $contactLeadProcess->save();
                PushContactWithZnsToCrm::dispatch($contactLeadProcess, ['status' => PushContactStatus::CREATE_BILL]);
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
//        $targetLink = env('PGW_PAYGATE').'/api/v1/zns/activeContact?id='.Crypt::encryptString($contactLeadProcess['id']);
//        $customUrl = Str::random('6').$contactLeadProcess['id'];
//        $link = ShortLink::handler($targetLink, $customUrl);

        return response()->json([
            'message' => 'save success',
            'data' => [
                'contact' => $contact,
                'contactLeadProcess' => $contactLeadProcess,
//                'link_active' => $link
            ]
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function show($id)
    {
        $contact = $this->contactProcessModel->find($id);

        return response()->json([
            'message' => 'get success',
            'data' => [
                'contact' => $contact
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(ContactLeadRequest $request, $id)
    {
        $data = $request->validated();

        $contact = $this->contactProcessModel->find($id);
        $contact->fill($data);
        $contact->save();

        return response()->json([
            'status' => true,
            'message' => 'update success',
            'data' => [
                'contact' => $contact
            ]
        ]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return 0;
        $contact = $this->mainModel->find($id);

        $contact->delete();

        return response()->json([
            'message' => 'delete success',
            'data' => [
            ]
        ]);
    }

    public function sendSms($contactLeadProcess)
    {
        try {
            $smsContent = "Xin chào bạn {full_name} Để hoàn tất quá trình đăng ký dùng thử phòng luyện thi ảo Easy IELTS Online Assessment,bạn vui lòng truy cập vào link:{link_active} .Link xác thực có thời hạn {date_expired} ngày.";
            $landingPage = LandingPage::find($contactLeadProcess['landing_page_id']);
            if ($landingPage) {
                $targetLink = env('PGW_PAYGATE') . '/api/v1/zns/activeContact?id=' . Crypt::encryptString($contactLeadProcess['id']);
                $customUrl = Str::random('6') . $contactLeadProcess['id'];
                $link_active = ShortLink::handler($targetLink, $customUrl);
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
    public function sendMail($contactLeadProcess){

        $targetLink = env('PGW_PAYGATE') . '/api/v1/zns/activeContact?id=' . Crypt::encryptString($contactLeadProcess['id']);
        $customUrl = Str::random('6') . $contactLeadProcess['id'];
        $link_active = ShortLink::handler($targetLink, $customUrl);

        $emailTemplateCode = 'verify_easy_ielts_online_assessment';
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

    public function exportContactExamNull(){
            $exportContactExamNull = DB::table('contact_lead_process')
                    ->leftjoin('contact_exams','contact_lead_process.id','=','contact_exams.contact_lead_process_id' )
                    ->where('landing_page_id','=', 89)
                    ->whereNull('contact_exams.id')
                    ->get();
                    return response()->json([
                        'status'=>true,
                        'message' => 'success',
                        'data' => [
                            'exportContactExamNull' => $exportContactExamNull
                        ]
                    ]);
    }
}
