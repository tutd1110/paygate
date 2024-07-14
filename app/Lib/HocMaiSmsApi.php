<?php

namespace App\Lib;

use App\Helper\Request;
use App\Models\SmsLog;
use Carbon\Carbon;

class HocMaiSmsApi
{
    private $apiLink = 'https://hocmai.vn/api/app/sendSms';
    private $token;


    public function __construct()
    {
        $this->token = config('hocmai.TOKEN_SEND_SMS');
    }

    /***
     * @param $phone
     * @param $message
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendSms($phone, $message, $objectId = 0, $objectInstance = '', $contact_lead_procees_id = 0)
    {
        /***
         * Tạo log sms
         */
        $smsLog = SmsLog::create([
            'phone' => $phone,
            'sms_content' => $message,
            'object_id' => $objectId,
            'object_instance' => $objectInstance,
            'sent_status' => 'create',
            'data' => '',
            'contact_lead_procees_id' => $contact_lead_procees_id,
            'sent_time' => Carbon::now()
        ]);

        try {
            $data = Request::post($this->apiLink, [
                'form_params' => [
                    'token' => $this->token,
                    'phone' => $phone,
                    'message' => $message,
                ]
            ]);
            $resData = json_decode((string)$data->getBody());

            if ($resData->status == 'success') {
                $smsLog->sent_status = 'sent';
                $smsLog->save();

                return true;
            } else {
                $smsLog->sent_status = 'sent_error';
                $smsLog->save();

                return false;
            }
        } catch (\Exception $exception) {
            $smsLog->sent_status = 'sent_error';
            $smsLog->save();

            return false;
        }
    }
}
