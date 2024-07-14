<?php

namespace App\Helper;

class SendSms
{
    static function send($phone, $message, $objectId = 0, $objectInstance = '', $contact_lead_procees_id = 0)
    {
        return app('hocmai_sms')->sendSms($phone, $message, $objectId, $objectInstance, $contact_lead_procees_id);
    }
}
