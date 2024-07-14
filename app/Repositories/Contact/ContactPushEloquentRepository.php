<?php

namespace App\Repositories\Contact;

use App\Helper\Request;
use App\Models\ContactLead;
use App\Models\ContactLeadProcess;
use App\Models\ContactPushPartner;

class ContactPushEloquentRepository implements ContactPushRepositoryInterface
{

    /****
     * @var ContactLead|mixed
     */
    private $contactLeadModel;


    private $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.hf1C6Hm3h1S7MGDmnp6DvKfxWM_efqM5dbQ6NPLbMec';


    public function __construct()
    {
        $this->contactLeadModel = app()->make(ContactLead::class);
    }

    public function handler(ContactLead $contactLead)
    {
        /***
         * check campaign mà contact đang đổ vào
         */
        $contactLead = $this->contactLeadModel->with('landingPage.apiPartners')->find($contactLead->id);

        if ($contactLead->landingPage) {
            foreach ($contactLead->landingPage->apiPartners as $partner) {
                $this->realPush($contactLead, $contactLead->landingPage, $partner);
            }
        }
    }

    private function realPush($contactLead, $landingPage, $partner)
    {
        switch ($partner->code) {
            case 'CRM-THPT':
            case 'CRM-THCS':
                {
                    $res = Request::request($partner->url, 'POST', [
                        'query' => [
                            'ldp' => $landingPage->code,
                        ],
                        'form_params' => [
                            'name' => $contactLead->full_name,
                            'phone' => $contactLead->phone,
                            'email' => $contactLead->email,
                            'address' => $contactLead->address,
                            'classid' => $contactLead->class,
                            'crm_info' => 1,
                        ],
                        'verify' => false,
                    ]);

                    $resData = json_decode($res->getBody());

                    if ($resData) {
                        if ($resData->status == 'success') {
                            /***
                             * log lại kết quả
                             */
                            foreach ($resData->crmResponseInfo as $eachInfo) {
                                $contactPushPartner = ContactPushPartner::create([
                                    'contact_lead_id' => $contactLead->id,
                                    'api_partner_id' => $partner->id,
                                    'contact_lead_process_id' => 0,
                                    'crm_id' => $eachInfo->crmID,
                                    'partner_contact_id' => $eachInfo->contactID ?? 0,
                                    'landing_page_contact_id' => $eachInfo->ldpContactID ?? 0,
                                    'reserve_contact_id' => $eachInfo->reserveContactID ?? 0,
                                    'extend_info' => json_encode($resData),
                                ]);
                            }
                        }
                    }
                }
                break;
            default:
                break;
        }
    }

    public function pushReserveContact(ContactLead $contactLead, array $extendData = [])
    {

        Request::post(
            config('hocmai.hocmai_url').'api/crm/addReserveContact',
            [
                'form_params' => [
                    'landing_page' => $contactLead->landingPage->code,
                    'user_id' => $contactLead->user_id,
                    'phone' => $contactLead->phone,
                    'status' => $contactLead->action_status,
                    'event' => $contactLead->landingPage->event,
                    'utm_source' => $contactLead->utm_source,
                    'utm_medium' => $contactLead->utm_medium,
                    'utm_content' => '',
                    'utm_campaign' => $contactLead->utm_campaign,
                    'session_id' => $extendData['session_id'],
                    'uri' => $extendData['uri'],
                    'fsuid' => $extendData['fsuid'],
                    'client_ip' => $contactLead->register_ip,
                    'token' => $this->token,
                ],
            ]
        );


    }

    /***
     * @param ContactLeadProcess $contactLeadProcess
     * @param array              $extendParam
     *                                       line string Dong san pham
     *                                       fromSystem String nguon contact doi voi phan shashi
     *
     */
    public function pushContactLeadProcess(ContactLeadProcess $contactLeadProcess, $extendParam = [])
    {
        /***
         * check campaign mà contact đang đổ vào
         */
        $contactLeadProcess = ContactLeadProcess::query()->with('landingPage.apiPartners')
            ->find($contactLeadProcess->id);
        if ($contactLeadProcess->landingPage) {
            foreach ($contactLeadProcess->landingPage->apiPartners as $partner) {
                $this->realPushContactLeadProcess($contactLeadProcess, $contactLeadProcess->landingPage, $partner,
                    $extendParam);
            }
        }
    }

    private function realPushContactLeadProcess(
        ContactLeadProcess $contactLeadProcess,
        $landingPage,
        $partner,
        $extendParam = []
    ) {
        switch ($partner->code) {
            case 'CRM-THPT':
            case 'CRM-THCS':
                {
                    $data = [
                        'name' => $contactLeadProcess->full_name,
                        'phone' => $contactLeadProcess->phone,
                        'email' => $contactLeadProcess->email,
                        'address' => $contactLeadProcess->address,
                        'classid' => $contactLeadProcess->class,
                        'crm_info' => 1,
                    ];
                    $query = [
                        'ldp' => $landingPage->code,
                    ];

                    if ($extendParam['line'] ?? false) {
                        $data['line'] = $extendParam['line'];
                    }
                    $res = Request::request($partner->url, 'POST', [
                        'query' => $query,
                        'form_params' => $data,
                        'verify' => false,
                    ]);

                    $resData = json_decode($res->getBody());

                    if ($resData) {
                        if ($resData->status == 'success') {
                            /***
                             * log lại kết quả
                             */
                            foreach ($resData->crmResponseInfo as $eachInfo) {
                                $contactPushPartner = ContactPushPartner::create([
                                    'contact_lead_id' => $contactLeadProcess->contact_lead_id,
                                    'api_partner_id' => $partner->id,
                                    'contact_lead_process_id' => $contactLeadProcess->id,
                                    'crm_id' => $eachInfo->crmID,
                                    'partner_contact_id' => $eachInfo->contactID ?? 0,
                                    'landing_page_contact_id' => $eachInfo->ldpContactID ?? 0,
                                    'reserve_contact_id' => $eachInfo->reserveContactID ?? 0,
                                    'extend_info' => json_encode($resData),
                                ]);
                            }
                        }
                    }
                }
                break;
            case 'CRM-SASHI-CODING':
            case 'CRM-SASHI-EASY-IELTS':
            case 'CRM-SASHI-HUONGNGHIEP':
                {
                    $data = [
                        'name' => $contactLeadProcess->full_name,
                        'phone' => $contactLeadProcess->phone,
                        'email' => $contactLeadProcess->email,
                        'address' => $contactLeadProcess->address,
                        'classid' => $contactLeadProcess->class,
                        'link' => $landingPage->domain_name,
                        'note' => $contactLeadProcess->description
                    ];
                    if ($extendParam['status'] ?? null) {
                        $mapStatus = $partner->map_status ?? [];
                        foreach ($mapStatus as $key => $eachStatus) {
                            if ($extendParam['status'] == $key) {
                                $data[$eachStatus['key']] =  $eachStatus['value'];
                                break;
                            }
                        }
                    }

                    if ($extendParam['line'] ?? '') {
                        $data['line'] = $extendParam['line'];
                    }

                    $res = Request::post($partner->url, [
                        'headers' => [
                            'api-key' => $partner->token,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => $data,
                        'verify' => false,
                    ]);

                    $resData = json_decode($res->getBody());

                    if ($resData) {
                        if ($resData->success) {
                            /***
                             * log lại kết quả khi push qua shashi
                             */
                            $contactPushPartner = ContactPushPartner::create([
                                'contact_lead_id' => $contactLeadProcess->contact_lead_id,
                                'api_partner_id' => $partner->id,
                                'contact_lead_process_id' => $contactLeadProcess->id,
                                'partner_contact_string_uuid' => $resData->result ?? '',
                                'extend_info' => json_encode($resData),
                            ]);
                        }

                    }
                }
                break;
            case 'CMS-ASSESSMENT':

            {
                if (!empty($contactLeadProcess->email)) {
                    $data = [
                        'email' => $contactLeadProcess->email,
                    ];
                    $res = Request::post($partner->url, [
                        'headers' => [
                            'x-api-key' => $partner->token,
                            'Content-Type' => 'application/json',

                        ],
                        'json' => $data,
                        'verify' => false,
                    ]);

                    $resData = json_decode($res->getBody());

                    if ($resData) {
                        if ($resData->status == 'success') {
                            /***
                             * log lại kết quả khi push qua assessment
                             */
                            $contactPushPartner = ContactPushPartner::create([
                                'contact_lead_id' => $contactLeadProcess->contact_lead_id,
                                'api_partner_id' => $partner->id,
                                'contact_lead_process_id' => $contactLeadProcess->id,
                                'extend_info' => json_encode($resData),
                            ]);
                        }
                    }
                }
            }
            default:
                break;
        }
    }


}
