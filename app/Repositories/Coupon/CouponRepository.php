<?php

namespace App\Repositories\Coupon;


use App\Helper\Request;
use App\Models\Traffic;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;

class CouponRepository implements CouponRepositoryInterface
{
    private $apiCheckCoupon = '';

    private $apiCreateCoupon = '';
    private $apiListCoupon = '';
    /***
     * @var Client
     */
    private $http;

    private $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.hf1C6Hm3h1S7MGDmnp6DvKfxWM_efqM5dbQ6NPLbMec';

    public function __construct()
    {
        $this->apiCheckCoupon = config('hocmai.hocmai_url').'/api/crm/checkReserve';
        $this->apiCreateCoupon = config('hocmai.hocmai_url').'/api/crm/reserve';
        $this->apiListCoupon = config('hocmai.hocmai_url').'/api/crm/listReserve';
        $this->http = new Client();
    }


    /***
     * @param $userId
     * @param $couponName
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkCoupon($userId, $couponName)
    {
        try {
            $res = Request::get($this->apiCheckCoupon, [
                'query' => [
                    'user_id' => $userId,
                    'coupon_name' => $couponName,
                    'token' => $this->token,
                ]
            ]);


            $resData = json_decode($res->getBody());


            if ($resData->status == true) {

                $resData->coupon->timelog = Carbon::createFromTimestamp($resData->coupon->timelog);
                $resData->coupon->startdate = Carbon::createFromTimestamp($resData->coupon->startdate);
                $resData->coupon->expire = Carbon::createFromTimestamp($resData->coupon->expire);

                return $resData->coupon;
            } else {
                return null;
            }

        } catch (\Exception $exception) {
            throw $exception;
        }

    }

    /***
     * @param $userId
     * @param $couponName
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getListCoupon($queryData)
    {
        try {
            $queryData['token'] = $this->token;
            $res = Request::get( $this->apiListCoupon , [
                'query' => $queryData,
                'headers' => [
                    'token' => $this->token,
                ]
            ]);


            $resData = json_decode($res->getBody());

            if ($resData->status == true) {
                foreach ($resData->listCoupons->data as $key => $value) {
                    if ($value->timelog) {
                        $resData->listCoupons->data[$key]->timelog = Carbon::createFromTimestamp($value->timelog)->toJSON();
                    }
                    if ($value->timeused) {
                        $resData->listCoupons->data[$key]->timeused = Carbon::createFromTimestamp($value->timeused)->toJSON();
                    }


                }


                return $resData->listCoupons;
            } else {
                return null;
            }

        } catch (\Exception $exception) {
            throw $exception;
        }

    }


    public function create(
        $landingName,
        $userId,
        $event,
        $preStartTime,
        $allowReserveStartTime,
        $startTime1,
        $startTime2,
        $endTime1,
        $endTime2,
        $reserveFee,
        $startCoupon,
        $endCoupon,
        $combo,
        $packageGroupName,
        $discount,
        $utmCampaign,
        $utmMedium,
        $utmSource,
        $sessionId,
        $fsuid,
        $uri,
        $emailSubject,
        $emailContent
    ) {
        $createRes = Request::post($this->apiCreateCoupon, [
            'form_params' => [
                'landing' => $landingName,
                'user_id' => $userId,
                'event' => $event,
                'pre_start_time' => $preStartTime,
                'allow_reserve_start_time' => $allowReserveStartTime,
                'start_time_1' => $startTime1,
                'start_time_2' => $startTime2,
                'end_time_1' => $endTime1,
                'end_time_2' => $endTime2,
                'reserve_fee' => $reserveFee,
                'start_coupon' => $startCoupon,
                'end_coupon' => $endCoupon,
                'combo' => $combo,
                'package_group_name' => $packageGroupName,
                'discount' => $discount,
                'utm_campaign' => $utmCampaign,
                'utm_medium' => $utmMedium,
                'utm_source' => $utmSource,
                'session_id' => $sessionId,
                'fsuid' => $fsuid,
                'uri' => $uri,
                'email_subject' => $emailSubject,
                'email_content' => $emailContent,
                'token' => $this->token,

            ]
        ]);

        $createResData = json_decode($createRes->getBody());

        if ($createResData) {
            $reserve = $createResData->reserve;

            switch ($reserve->status) {
                case 'not-enough' :
                    throw new \Exception('Tài khoản của bạn không đủ tiền!', 402);
                case 'booked' :
                    throw new \Exception('Coupon đã được tạo!', 201);
                case 'success':
                    return $reserve->coupon;
                case 'error':
                    throw new \Exception($reserve->message, 400);
            }
        } else {
            throw new \Exception('Lỗi hệ thống xin thử lại!');
        }
    }

}
