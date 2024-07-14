<?php

namespace App\Http\Controllers\Api;

use App\Helper\RedisHelper;
use App\Http\Controllers\Controller;
use App\Jobs\PushContact\PushContactWithZnsToCrm;
use App\Lib\PushContactStatus;
use App\Models\ContactLeadProcess;
use App\Models\SendZaloZns;
use App\Repositories\ZNS\ZnsInterface;
use App\Repositories\ZNS\ZnsRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use PHPUnit\Util\Exception;

class ZaloNotificationSmsController extends Controller
{
    function __construct(ZnsInterface $ZnsRes)
    {
        $this->znsRes = $ZnsRes;
    }
    function redirectOAURL(Request $request)
    {
        $url = $this->znsRes->redirectOAURL();
        return Redirect::to($url);
    }

    function callback(Request $request)
    {
//        dd(1);
        $callback = $this->znsRes->callback($request->code);
        return $callback;
    }

    function sendZns(Request $request)
    {
        $this->znsRes->sendZns($request);
    }
    function sendZnsFpt(Request $request)
    {
        $this->znsRes->sendZnsFpt($request);
    }

    public function activeContact(Request $request)
    {
        try {
            $id = $request['id'];
            if (empty($id)) {
                return $this->notifyZNS('error', 'ID không hợp lệ');
            }
            $id = Crypt::decryptString($id);
            $contactLeadProcess = ContactLeadProcess::find($id);

            if (empty($contactLeadProcess)){
                $this->notifyZNS('error', 'Khách hàng không tồn tại');
            }
            $contactLeadProcess->verified = 'yes';
            $contactLeadProcess->save();
            PushContactWithZnsToCrm::dispatch($contactLeadProcess, ['status' => PushContactStatus::CREATE_BILL]);
            return $this->notifyZNS('success', 'Xác thực khách hàng thành công!');
        } catch (\Exception $exception) {
            return $this->notifyZNS('error','Lỗi không xác định');
        }
    }

    function notifyZNS($status,$message)
    {
        session()->flash($status,$message);
        return view('verified_zns.notify_zns');
    }

}
