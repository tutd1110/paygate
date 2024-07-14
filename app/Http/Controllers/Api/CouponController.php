<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coupon\CouponRequest;
use App\Http\Requests\Coupon\ListCoupon;
use App\Models\LandingPage;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Coupon\CouponRepositoryInterface;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CouponController extends Controller
{
    /***
     * @var CouponRepositoryInterface | CouponRepository
     */
    private $couponRepo;

    public function __construct(CouponRepositoryInterface $couponRepo)
    {
        $this->couponRepo = $couponRepo;
    }

    public function index(ListCoupon $request) {
        $landingPage = LandingPage::find($request->get('landing_page_id', 0));

        if ($landingPage) {

            $data = [];

            $data['event'] = $landingPage->event;

            if ($request->get('page', 0))  {
                $data['page'] = $request->get('page', 0);
            }

            if ($request->get('begin_time', 0)) {
                $data['begin_time'] = Carbon::createFromFormat('Y-m-d', $request->get('begin_time', 0))
                    ->startOfDay()->timestamp;
            }
            if ($request->get('end_time', 0)) {
                $data['end_time'] = Carbon::createFromFormat('Y-m-d', $request->get('end_time', 0))
                    ->endOfDay()->timestamp;;
            }
            if ($request->get('is_used', 0))  {
                $data['is_used'] = $request->get('is_used', 0);
            }

            if ($request->get('begin_time_use', 0)) {
                $data['begin_time_use'] = Carbon::createFromFormat('Y-m-d', $request->get('begin_time_use', 0))
                    ->startOfDay()->timestamp;
            }
            if ($request->get('end_time_use', 0)) {
                $data['end_time_use'] = Carbon::createFromFormat('Y-m-d', $request->get('end_time_use', 0))
                    ->endOfDay()->timestamp;;
            }

            $coupons = $this->couponRepo->getListCoupon($data);

            return response()->json([
                'message' => 'get success',
                'data' => [
                    'coupons' => $coupons
                ]
            ]);

        } else {
            return response()->json([
                'message' => 'Landing page not found'
            ], 404);
        }
    }


    public function checkCoupon(Request $request)
    {

        $landingPage = LandingPage::find($request->get('landing_page_id', 0));

        if ($landingPage) {
            $coupon = $this->couponRepo->checkCoupon($request->get('user_id', 0), $landingPage->coupon_name);

            if ($coupon) {
                return response()->json([
                    'message' => 'get success',
                    'data' => [
                        'coupon' => $coupon
                    ]
                ]);
            } else {
                return response()->json([
                    'message' => 'coupon not found',
                    'data' => [
                        'coupon' => null
                    ]
                ]);
            }

        } else {
            return response()->json([
                'message' => 'Landing page not found'
            ], 404);
        }
    }


    public function storage(CouponRequest $request)
    {

        $landing = LandingPage::find($request->input('landing_page_id'));

        if ($landing) {
            try {
                $coupon = $this->couponRepo->create(
                    $landing->code,
                    $request->input('user_id'),
                    $landing->event,
                    $request->input('pre_start_time'),
                    $request->input('allow_reserve_start_time'),
                    $request->input('start_time_1'),
                    $request->input('start_time_2'),
                    $request->input('end_time_1'),
                    $request->input('end_time_2'),
                    $request->input('reserve_fee'),
                    $request->input('start_coupon'),
                    $request->input('end_coupon'),
                    $request->input('combo'),
                    $request->input('package_group_name'),
                    $request->input('discount'),
                    $request->input('utm_campaign'),
                    $request->input('utm_medium'),
                    $request->input('utm_source'),
                    $request->input('session_id'),
                    $request->input('fsuid'),
                    $request->input('uri'),
                    $request->input('email_subject', ''),
                    $request->input('email_content', '')
                );

                return response()->json([
                    'message' => 'create coupon success',
                    'code' => 'success',
                    'data' => [
                        'coupon' => $coupon,
                    ]
                ]);
            } catch (\Exception $exception) {
                switch ($exception->getCode()) {
                    case 201:
                        $codeText = 'booked';

                        return response()->json([
                            'code' => $codeText,
                            'message' => $exception->getMessage(),
                        ], JsonResponse::HTTP_BAD_REQUEST);
                    case 402:
                        $codeText = 'not-enough-money';

                        return response()->json([
                            'code' => $codeText,
                            'message' => $exception->getMessage(),
                        ], JsonResponse::HTTP_BAD_REQUEST);
                    default:
                        $codeText = 'unknown error';

                        return response()->json([
                            'code' => $codeText,
                            'message' => $exception->getMessage(),
                        ], JsonResponse::HTTP_BAD_REQUEST);
                }


            }


        } else {
            return response()->json([
                'message' => 'landing page id not found'
            ], 400);
        }
    }
}
