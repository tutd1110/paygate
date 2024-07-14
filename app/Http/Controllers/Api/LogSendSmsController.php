<?php

namespace App\Http\Controllers\Api;

use App\Helper\SendSms;
use App\Http\Controllers\Controller;
use App\Http\Requests\SMS\SendSmsRequest;
use Illuminate\Http\Request;

class LogSendSmsController extends Controller
{
    public function sendSms(SendSmsRequest $request)
    {
        $sendSms = SendSms::send($request->phone, $request->sms_content, 0 , get_class((object)$this) , $request->contact_lead_process_id);
        if ($sendSms){
            return response()->json([
                'status' => true,
                'message' => 'send SMS success',
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'send SMS fail',
            ]);
        }
    }
}
