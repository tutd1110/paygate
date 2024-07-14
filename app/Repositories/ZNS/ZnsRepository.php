<?php

namespace App\Repositories\ZNS;

use App\Helper\Mycurl;
use App\Helper\RedisHelper;
use App\Helper\Request;
use App\Helper\ShortLink;
use App\Models\SendZaloZns;
use App\Models\TemplateZaloZnsLanding_page;
use App\Models\TemplateZaloZnsLandingPage;
use Carbon\Carbon;
use App\Exceptions\InvalidArgumentException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use App\Helper\RandomHelper;
use PHPUnit\Util\Exception;

class ZnsRepository implements ZnsInterface
{
    const CODE_SUCCESS_ZNS= 0;
    const CODE_SUCCESS_ZNS_FPT = 1;
    const MESSAGE_SUCCESS_ZNS = 'Success';

    function __construct()
    {
        $this->get_access_token = 'https://oauth.zaloapp.com/v4/oa/access_token';
        $this->url_get_authorization_code = 'https://oauth.zaloapp.com/v4/oa/permission';
        $this->url_callback = config('services.ZNS.redirect_uri');
    }

    function redirectOAURL()
    {
        try {
            #Tạo code_challenge và code_verifier lưu trong redis
            $codeVerifier = $this->genCodeVerifier();
            $codeChallenge = $this->genCodeChallenge($codeVerifier);
            $paramZnsRedis = [
                'code_challenge' => $codeChallenge,
                'code_verifier' => $codeVerifier
            ];
            RedisHelper::set('paramCodeZNS', json_encode($paramZnsRedis));

            $paramCallback = [
                'app_id' => config('services.ZNS.app_id'),
                'redirect_uri' => $this->url_callback,
                'code_challenge' => $codeChallenge,
            ];
            $url = $this->url_get_authorization_code . '?' . http_build_query($paramCallback, '', '&');
            return $url;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    function callback($code = null)
    {

        $codeVerifier = json_decode(RedisHelper::get('paramCodeZNS'), true)['code_verifier'] ?? null;
        $codeChallenge = json_decode(RedisHelper::get('paramCodeZNS'), true)['code_challenge'] ?? null;

        if (empty($code)) {
            throw new Exception('Không có mã AUTHORIZATION_CODE');
        }
        if (empty($codeVerifier) || empty($codeChallenge)) {
            throw new Exception('Ứng dụng chưa có code_verifier hoặc code_challenge');
        }
        try {
            $param = [
                'headers' => [
                    'secret_key' => config('services.ZNS.secret_key'),
                ],
                'form_params' => [
                    'code' => $code,
                    'app_id' => config('services.ZNS.app_id'),
                    'grant_type' => 'authorization_code',
                    'code_verifier' => json_decode(RedisHelper::get('paramCodeZNS'), true)['code_verifier']
                ]
            ];

            #Lấy access_token và refresh_token lưu vào trong Redis
            $getAccessToken = \App\Helper\Request::post($this->get_access_token, $param);
            $getParamZns = json_decode((string)$getAccessToken->getBody(), true);
            $paramRedis = [
                'access_token' => $getParamZns['access_token'],
                'refresh_token' => $getParamZns['refresh_token'],
                'date' => strtotime("now")
            ];
            RedisHelper::set('paramKeyZNS', json_encode($paramRedis));
            return response()->json([
                'status' => true,
                'message' => 'Đã tạo access_token thành công',
            ]);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    private function refreshAccessToken($refresh_access_token)
    {
        $param = [
            'headers' => [
                'secret_key' => config('services.ZNS.secret_key'),
            ],
            'form_params' => [
                'refresh_token' => $refresh_access_token,
                'app_id' => config('services.ZNS.app_id'),
                'grant_type' => 'refresh_token'
            ]
        ];
        $getAccessToken = \App\Helper\Request::post($this->get_access_token, $param);
        $getParamZns = json_decode((string)$getAccessToken->getBody(), true);
        $paramRedis = [
            'access_token' => $getParamZns['access_token'],
            'refresh_token' => $getParamZns['refresh_token'],
            'date' => strtotime("now")
        ];
        RedisHelper::set('paramKeyZNS', json_encode($paramRedis));
        return $paramRedis;
    }

    private function getAccessToken()
    {
        $paramZnsRedis = json_decode(RedisHelper::get('paramKeyZNS'), true);
        if (!empty($paramZnsRedis)) {
            if (strtotime("now") - $paramZnsRedis['date'] > 86400) {
                $paramZns = $this->refreshAccessToken($paramZnsRedis['refresh_token']);
                return $paramZns['access_token'];
            }
            return $paramZnsRedis['access_token'];
        } else {
            throw new Exception('Chưa khởi tạo key cho ứng dụng ZNS!');
        }
    }

    /***
     * Tạo đơn hàng
     */

    public static function genCodeVerifier()
    {
        $random = bin2hex(openssl_random_pseudo_bytes(32));
        return self::base64url_encode(pack('H*', $random));
    }

    /**
     * generates code challenge
     *
     * @param $codeVerifier
     * @return string
     */
    public static function genCodeChallenge($codeVerifier)
    {
        if (!isset($codeVerifier)) {
            return '';
        }

        return self::base64url_encode(pack('H*', hash('sha256', $codeVerifier)));
    }

    private static function base64url_encode($plainText)
    {
        $base64 = base64_encode($plainText);
        $base64 = trim($base64, "=");
        return strtr($base64, '+/', '-_');
    }

    private function getTemplateData($landingPageID,$contactLeadProcess){

        // param ban đầu
        $targetLink = env('PGW_PAYGATE').'/api/v1/zns/activeContact?id='.Crypt::encryptString($contactLeadProcess['id']);
        $customUrl = Str::random('6').$contactLeadProcess['id'];
        $link = ShortLink::handler($targetLink, $customUrl);
        $paramTemplateData = [
            "Customer_Name" => $contactLeadProcess['full_name'] ?? null,
            "service_name" => 'Test ZNS',
            "xx" => $contactLeadProcess['date_expired'] ?? 0,
            "code" => $contactLeadProcess['code'] ?? null,
            "customer_id" => $contactLeadProcess['id'],
            "link_active" => $link,
            "user_id" => $contactLeadProcess['user_id'] ?? null
        ];
        //Lọc và chỉnh sửa param gửi đi theo template
        $templateZaloZnsLandingPage = TemplateZaloZnsLandingPage::where('landing_page_id', $landingPageID)->first();
        $templateID = $templateZaloZnsLandingPage['template_id'] ?? null;
        $templateData = !empty($templateZaloZnsLandingPage['template_data']) ? json_decode($templateZaloZnsLandingPage['template_data'], true) : null;

        if (empty($templateID) || empty($templateData)) {
            throw new \Exception('LandingPage chưa có mẫu template ZNS!');
        }

        $paramUnset = array_diff_key($paramTemplateData,$templateData);
        foreach ($paramUnset as $key => $value){
              unset($paramTemplateData[$key]);
        }
        return [
            'template_id' => $templateID,
            'template_data' => $paramTemplateData,
        ];
    }

    //Gửi ZNS
    function sendZns($contactLeadProcess = null)
    {
        try {
            $access_token = $this->getAccessToken();
            $param = [
                'headers' => [
                    'access_token' => $access_token,
                ],
                'json' => [
                    'phone' => '84987654321',
                    'template_id' => "220820",
                    'template_data' => [
                        "ky" => "1",
                        "thang" => "4/2020",
                        "start_date" => "20/03/2020",
                        "end_date" => "20/04/2020",
                        "customer" => "Nguyễn Thị Hoàng Anh",
                        "cid" => "PE010299485",
                        "address" => "VNG Campus, TP.HCM",
                        "amount" => "100",
                        "total" => "100000",
                    ]
                ],
                "tracking_id"=>"tracking_id"
            ];
            //Lưu param vào bảng log SendZaloZns
            $sendZaloZns = SendZaloZns::create([
                'landing_page_id' => $contactLeadProcess['landing_page_id'] ?? 0,
                'contact_lead_processs_id' => $contactLeadProcess['contact_lead_processs_id'] ?? 0,
                'headers' => json_encode($param['headers']),
                'to_phone' => $param['json']['phone'] ?? null,
                'template_id' => $param['json']['template_id'] ?? null,
                'template_data' => !empty($param['json']['template_data']) ? json_encode($param['json']['template_data']) : null,
                'status' => 'create',
            ]);
            $sendZns= \App\Helper\Request::post(config('services.ZNS.url_send_message'), $param);
            $sendZns =json_decode((string)$sendZns->getBody(), true);
            if ($sendZns['error'] == 0 && $sendZns['message'] == 'success') {
                $sendZaloZns['sent_time'] = Carbon::now();
                $sendZaloZns['status'] = 'sent';
                $sendZaloZns['response'] = $sendZns;
                $sendZaloZns->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Gửi ZNS thành công!'
                ]);
            } else {
                $sendZaloZns['sent_time'] = Carbon::now();
                $sendZaloZns['status'] = 'sent_error';
                $sendZaloZns['response'] = $sendZns;
                $sendZaloZns->save();
                throw new \Exception('Gửi ZNS thất bại') ;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    //Gửi message ZNS qua đại lí FPT

    /** @param null $event : Sự kiện cần gửi qua ZNS */
    function sendZnsFpt($contactLeadProcess = null, $event = null)
    {
        try {
            $array_template = $this->getTemplateData($contactLeadProcess['landing_page_id'], $contactLeadProcess);
            if (empty($event)) {
                throw new \Exception('Chưa có thông tin của ZNS!');
            }
            $headers = [
                'app-id' => config('services.ZNS_FPT.' . $event . '.app_id'),
                'secret-key' => config('services.ZNS_FPT.' . $event . '.secret_key'),
            ];
            $param = [
                'headers' => $headers,
                'json' => [
                    'phone' => $contactLeadProcess['phone'],
                    'template_id' => $array_template['template_id'],
                    'template_data' => $array_template['template_data']
                ],
                'ref_id' => 'ref_id'
            ];
            //Lưu param vào bảng log SendZaloZns
            $sendZaloZns = SendZaloZns::create([
                'landing_page_id' => $contactLeadProcess['landing_page_id'] ?? 0,
                'contact_lead_processs_id' => $contactLeadProcess['contact_lead_processs_id'] ?? 0,
                'headers' => json_encode($param['headers']),
                'to_phone' => $param['json']['phone'] ?? null,
                'template_id' => $param['json']['template_id'] ?? null,
                'template_data' => !empty($param['json']['template_data']) ? json_encode($param['json']['template_data']) : null,
                'status' => 'create',
            ]);
            $sendZns= \App\Helper\Request::post(config('services.ZNS_FPT.url_send_message'), $param);
            $sendZns =json_decode((string)$sendZns->getBody(), true);
            if ($sendZns['code'] == self::CODE_SUCCESS_ZNS_FPT && $sendZns['message'] == self::MESSAGE_SUCCESS_ZNS) {
                $sendZaloZns['sent_time'] = Carbon::now();
                $sendZaloZns['status'] = 'sent';
                $sendZaloZns['response'] = $sendZns;
                $sendZaloZns->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Gửi ZNS thành công!',
                ]);
            } else {
                $sendZaloZns['sent_time'] = Carbon::now();
                $sendZaloZns['status'] = 'sent_error';
                $sendZaloZns['response'] = $sendZns;
                $sendZaloZns->save();
                throw new \Exception('Gửi ZNS thất bại') ;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

}
