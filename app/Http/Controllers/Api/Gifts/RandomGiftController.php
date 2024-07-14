<?php

namespace App\Http\Controllers\Api\Gifts;

use App\Helper\NewRandomHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gifts\RunWheelFahasaRequest;
use App\Jobs\SendEmailHocMai;
use App\Lib\Wheel;
use App\Models\ContactLeadProcess;
use App\Models\EmailSave;
use App\Models\EmailTemplates;
use App\Models\Gifts\GiftCoupon;
use App\Models\Gifts\RandomGift;
use App\Models\Gifts\RandomGiftContact;
use App\Models\Gifts\Ticket;
use App\Repositories\UserBuy\UserBuyRepository;
use App\Repositories\UserBuy\UserBuyRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use wataridori\BiasRandom\BiasRandom;

class RandomGiftController extends Controller
{

    /****
     * @var UserBuyRepositoryInterface | UserBuyRepository
     */
    private $buyRepository;

    public function __construct(UserBuyRepositoryInterface $buyRepository)
    {
        set_time_limit(10000);
        $this->buyRepository = $buyRepository;
    }

    private $checkBuyBefore = true;

    public function getInfo(Request $request)
    {
        $filter = $request->all();

        $query = RandomGift::query();

        if (isset($filter['landing_page_id'])) {
            if (is_array($filter['landing_page_id'])) {
                $query = $query->whereIn('landing_page_id', $filter['landing_page_id']);
            } else {
                $query = $query->where('landing_page_id', $filter['landing_page_id']);
            }
        }
        if (isset($filter['type'])) {
            if (is_array($filter['type'])) {
                $query = $query->whereIn('type', $filter['type']);
            } else {
                $query = $query->where('type', $filter['type']);
            }
        }
        if (isset($filter['status'])) {
            if (is_array($filter['status'])) {
                $query = $query->whereIn('status', $filter['status']);
            } else {
                $query = $query->where('status', $filter['status']);
            }
        }
        if (isset($filter['order_by'])) {
            if (is_array($filter['order_by'])) {
                foreach ($filter['order_by'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            } else {
                $filter['order_by'] = explode(',', $filter['order_by']);
                $filter['direction'] = explode(',', $filter['direction']);
                foreach ($filter['order_by'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            }
        }
        $listGift = $query->where('status', 'active')->get();
        return response()->json([
            'message' => 'get success',
            'data' => [
                'gift' => $listGift->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'thumb' => $item->thumb,
                        'quantity' => $item->quantity,
                        'landing_page_id' => $item->landing_page_id
                    ];
                }),
            ]
        ]);
    }

    public function testUser($userId)
    {
        if (in_array($userId, [
            508218,
            6204889,
            6492435,
            5480988
        ])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * runWheelFahasaNew function - lấy kết quả vòng quay
     *
     * @param RunWheelFahasaRequest $request
     * @return mixed
     */
    public function runWheelFahasaNew(RunWheelFahasaRequest $request)
    {
        $filter = $request->validated();
        try {
//            DB::beginTransaction();
            #1. Kiểm tra bill code xem đã được dùng chưa?
            $checkTicketApplied = Ticket::query()
                ->where('bill_code', $filter['bill_code'])
                ->where('status', Ticket::STATUS_APPROVED)
                ->where('lock',Ticket::YES_LOCK)
                ->first();

            if (!empty($checkTicketApplied)) {
                return response()->json([
                    'status' => 'fail',
                    'error' => 'bill_applied',
                    'message' => 'Mã hóa đơn đã được sử dụng, vui lòng sử dụng hóa đơn khác.',
                    'data' => []
                ]);
            }

            #2. Kiểm tra ticket được verify chưa?
            $ticket = Ticket::query()
                ->where('bill_code', $filter['bill_code'])
                ->where('contact_lead_process_id', $filter['contact_id'])
                ->where('status', Ticket::STATUS_VERIFIED)
                ->first();

            if (empty($ticket)) {
                return response()->json([
                    'status' => 'fail',
                    'error' => 'ticket_not_verified',
                    'message' => 'Lượt quay chưa được xác thực vui lòng kiểm tra lại.',
                    'data' => []
                ]);
            }

            #2.1 Lấy thông tin contact
            $contact = ContactLeadProcess::query()->where('id', $filter['contact_id'])->first();
            if (empty($contact)) {
                return response()->json([
                    'status' => 'fail',
                    'error' => 'contact_not_found',
                    'message' => 'Không tìm thấy thông tin khách hàng.',
                    'data' => []
                ]);
            }

            #3. Lấy danh sách quà active giai đoạn này
            $randomGift = RandomGift::where('landing_page_id', $filter['landing_page_id'])
                ->where('status', RandomGift::STATUS_ACTIVE)
                ->get();

            if (empty($randomGift->toArray())) {
                return response()->json([
                    'status' => 'fail',
                    'error' => 'gift_not_found',
                    'message' => 'Không tìm thấy danh sách quà tặng.',
                    'data' => []
                ]);
            }

            #4. Phân chia tỷ lệ quà theo khu vực và thời gian, setup các điều kiện ra quà
            $now             = time();
            $configRunWheels = config('hocmai.VQMM_FAHASA');
            $startDatePart1  = strtotime($configRunWheels['START_TIME_FIRST']);
            $endDatePart1    = strtotime($configRunWheels['END_TIME_FIRST']);
            $startDatePart2  = strtotime($configRunWheels['START_TIME_FINAL']);
            $endDatePart2    = strtotime($configRunWheels['END_TIME_FINAL']);
            $giftHBTP        = array_filter(explode(',', $configRunWheels['ARRAY_GIFTS_HBTP']));
            $ignoreGift      = array_filter(explode(',', $configRunWheels['IGNORE_GIFT']));
            $excludeGifts    = $ignoreGift;

            #4.1 nếu ngày hiện tại thuộc giai đoạn 1
            if (!empty($startDatePart1) && !empty($endDatePart1) && ($now >= $startDatePart1 && $now <= $endDatePart1)) {
                $finalGiftSent = RandomGiftContact::query()->whereIn('gift_id', $giftHBTP)->first();
                #nếu chưa có quà nào được tặng => check điều kiện quà theo nhà sách
                if (empty($finalGiftSent)) {
                    if (!empty($configRunWheels['ADDRESS_ONSIDE']) && trim(strtoupper($ticket->store_name)) != trim(strtoupper($configRunWheels['ADDRESS_ONSIDE']))) {
                        $excludeGifts = $giftHBTP;
                    }
                } else {
                    $excludeGifts = $giftHBTP;
                }
            }

            #4.2 nếu ngày hiện tại thuộc giai đoan 2
            if (!empty($startDatePart2) && !empty($endDatePart2) && $now >= $startDatePart2 && $now <= $endDatePart2) {
                if (empty($configRunWheels['ADDRESS_ONSIDE']) || (trim(strtoupper($ticket->store_name)) == trim(strtoupper($configRunWheels['ADDRESS_ONSIDE'])))) {
                    $excludeGifts = [];
                }
            }

            #4.3 Kiểm tra các quà đã hết số lượng
            $randomGiftSort = $listRandomGift = [];
            $totalRate = 0;
            foreach ($randomGift as $key => $value) {
                $totalRate += $value['rate'];
                $randomGiftSort[$value['id']] = $value;
                if ($value['quantity'] <= $value['quantity_use']) {
                    $excludeGifts[] = strval($value['id']);
                    continue;
                }
                $listRandomGift[$value['id']] = $value;
            }

            #5. Tiến hành loại bỏ quà ra khỏi vòng quay
            if (!empty($excludeGifts)) {
                $excludeGifts = array_values(array_unique($excludeGifts));
                foreach ($excludeGifts as $value) {
                    $randomGiftSort[$value]['quantity_use'] = $randomGiftSort[$value]['quantity'];
                    unset($listRandomGift[$value]);
                }
            }

            if (empty($listRandomGift)) {
                return response()->json([
                    'status' => 'fail',
                    'error' => 'wheel_gift_empty',
                    'message' => 'Không còn quà tặng nào.',
                    'data' => []
                ]);
            }

            #6. Tính tỷ lệ ra quà
            $sumPercent = 0;
            foreach ($randomGiftSort as $id => $info) {
                if (in_array($id, $excludeGifts)) {
                    $sumPercent += $info['rate'];
                }
            }
            #6.1. Kiểm tra tổng phần trăm quà còn lại
            $totalPercentRemaining = 100 - $sumPercent;
            if ($totalPercentRemaining <= 0){
                return response()->json([
                    'status' => 'fail',
                    'error' => 'wheel_gift_empty',
                    'message' => 'Không còn quà tặng nào.',
                    'data' => []
                ]);
            }
            #6.1 tạo dữ liệu vòng quay + tỷ lệ ra quà sau khi loại trừ các quà
            $wheelData = [];
            foreach ($listRandomGift as $key => $value) {
                $giftRate = $value['rate'] + ($sumPercent / $totalPercentRemaining * $value['rate']);
                $wheelData[$value['id']] = $giftRate ;
            }

            #7. Random quà
            $giftId = $this->getWheelResult($wheelData);
            if (!$giftId) {
                return response()->json([
                    'status' => 'fail',
                    'error' => 'wheel_error_random_gift',
                    'message' => 'Không tìm thấy kết quả, vui lòng thử lại.',
                    'data' => []
                ]);
            }

            #7.1 Lấy thông tin quà
            $randomGiftSelect = RandomGift::where('id', $giftId)->first();
            if (empty($randomGiftSelect)) {
                return response()->json([
                    'status' => 'fail',
                    'error' => 'gift_not_found_2',
                    'message' => 'Không tìm thấy thông tin quà tặng.',
                    'data' => []
                ]);
            }

            #7.2 Tạo thông tin quà tặng tương ứng với contact
            $randomGiftContact = RandomGiftContact::create([
                'landing_page_id' => $filter['landing_page_id'],
                'contact_id' => $filter['contact_id'],
                'ticket_id' => $ticket->id,
                'gift_id' => $giftId,
            ]);
            if (empty($randomGiftContact)) {
                return response()->json([
                    'status' => 'fail',
                    'error' => 'save_gift_contact_error',
                    'message' => 'Lưu kết quả quà tặng không thành công.',
                    'data' => []
                ]);
            }

            #8. Tiến hành gửi mail quà tặng với các khách hàng có email
            if (!empty($contact['email'])) {
                #8.1 Kiểm tra xem quà tặng có phải coupone code? Nếu có lưu coupon code
                $couponCode = GiftCoupon::query()
                    ->where('gift_id', $giftId)
                    ->where('used', '=', 'no')
                    ->first();

                if (!empty($couponCode)) {
                    $randomGiftContact->coupon_code = $couponCode['code'];
                    $randomGiftContact->save();

                    #8.2 Lưu coupon code đã sử dụng
                    $couponCode->used = 'yes';
                    $couponCode->save();
                }

                #8.3 Gửi email tới khách hàng
                $templateEmail = EmailTemplates::where('code', 'fahasa_vqmm')->first();
                if (!empty($templateEmail)) {
                    if ($randomGiftSelect['type'] == RandomGift::TYPE_PRODUCT) {
                        $emailContent = str_replace('{{srcImageGift}}', ('https://hevuichoi.ican.vn/wp-content/uploads/sites/25/2023/05/Email.png'), htmlspecialchars_decode($templateEmail->content));
                        $randomGiftSelectName = $randomGiftSelect['name'] . ' khi tham gia <span style="color:#000000;font-weight:700;">"Vòng quay may mắn"</span> thuộc chương trình <span style="color:#000000;font-weight:700;">"Hè vui chơi - không rơi kiến thức"</span>';
                    } else {
                        $emailContent = str_replace('{{srcImageGift}}', ('https://hevuichoi.ican.vn/wp-content/uploads/sites/25/2023/05/LayoutEmail.png'), htmlspecialchars_decode($templateEmail->content));
                        $randomGiftSelectName = $randomGiftSelect['name'];
                    }

                    $emailContent = str_replace('{{full_name}}', ($contact['full_name'] ?? ''), $emailContent);
                    $emailContent = str_replace('{{gift_name}}', ($randomGiftSelectName ?? ''), $emailContent);
                    $emailContent = str_replace('{{description}}', ($randomGiftSelect['description'] ?? ''), $emailContent);
                    $couponCodeTemplate = '<p style="margin-bottom: 0;">Mã code học bổng: <span style="color:#000000;font-weight:700;font-size: 18px;">{{coupon_code}}</span></p>';
                    $productGiftNotification = '<p style="margin-bottom: 0;">Quý khách vui lòng giữ lại hoá đơn mua hàng số <span style="color:#000000;font-weight:700;font-size: 18px;">{{bill_code}}</span> có giá trị <span style="color:#000000;font-weight:700;font-size: 18px;">{{bill_value}} để làm căn cứ nhận giải</span></p>';

                    if (!empty($randomGiftContact['coupon_code'])) {
                        $emailContent = str_replace('{{coupon_code}}', ($randomGiftContact['coupon_code'] ?? ''), $emailContent);
                        $emailContent = str_replace($productGiftNotification, '', $emailContent);
                    } else {
                        $emailContent = str_replace($couponCodeTemplate, '', $emailContent);
                        $emailContent = str_replace('{{bill_code}}', ($ticket['bill_code'] ?? ''), $emailContent);
                        $emailContent = str_replace('{{bill_value}}', (number_format($ticket['bill_value']).' đ' ?? ''), $emailContent);
                    }
                    $sendEmailParams = [
                        'landing_page_id' => $ticket['landing_page_id'],
                        'contact_lead_process_id' => $ticket['contact_lead_process_id'],
                        'from_email' => 'noreply@hocmai.vn',
                        'from_name' => 'Hệ thống giáo dục HOCMAI',
                        'to_email' => $contact['email'],
                        'to_name' => $contact['full_name'],
                        'subject' => $templateEmail->subject,
                        'content' => $emailContent,
                        'file_attach' => 'nullable|string',
                    ];
                    if (!empty(config('hocmai.ADMIN.is_send'))) {
                        $email = EmailSave::create($sendEmailParams);
                        SendEmailHocMai::dispatch($email)->delay(Carbon::now()->addSeconds(20));
                    }
                }
            }

            #9. Nếu là quà coupon có email và quà product thì giảm số lượng
            if (!empty($randomGiftContact['coupon_code']) || $randomGiftSelect['type'] == RandomGift::TYPE_PRODUCT) {
                $randomGiftSelect->quantity_use += 1;
                $randomGiftSelect->save();
            }

            if (!empty(config('hocmai.ADMIN.is_send'))) {
                $this->checkQuantityCouponCode($giftId, $randomGiftSelect);
            }

            #10. Cập nhật trạng thái ticket sang approved - bill code đã khóa.
            $ticket->status = Ticket::STATUS_APPROVED;
            $ticket->save();
//            DB::commit();

            #11. Trả kết quả vòng quay - Nếu kết quả là coupon thì quy hết về chúc may mắn lần sau.
            $finalGift = $randomGiftSelect->toArray();
            if ($randomGiftSelect['type'] == RandomGift::TYPE_COUPON) {
                $randomGiftSelect = RandomGift::where('id', config('hocmai.VQMM_FAHASA.ID_CBMM_FAHASA'))->first();
            }

            #success
            return response()->json([
                'message' => 'get success',
                'status' => 'true',
                'data' => [
                    'gift' => $randomGiftSelect,
                    'finalGift' => !empty($filter['test']) ? $finalGift : [],
                ]
            ]);

        } catch (\Throwable $exception) {
//            DB::rollBack();
            return response()->json([
                'status' => 'fail',
                'error' => 'system_error',
                'message' => $exception->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * shuffle_assoc function - Random vị trí tỉ lệ trong mảng trước khi quay
     *
     * @param $array
     * @return mixed
     */
    public function shuffle_assoc($array)
    {
        while (count($array)) {
            $keys = array_keys($array);
            $index = $keys[random_int(0, count($keys) - 1)];
            $array_rand[$index] = $array[$index];
            unset($array[$index]);
        }
        asort($array_rand);
        return $array_rand;
    }

    /**
     * getWheelResult function - getWheelResult
     *
     * @param array $wheelData
     * @return mixed
     */
    private function getWheelResult($wheelData = array())
    {
        if (empty($wheelData)) {
            return false;
        }

        #6. Check điều kiện ra quà đặc biệt - nếu có quà đặc biệt có trong mảng quà và khớp thời gian => tăng tỷ lệ ra quà đặc biệt.
        $specialGift   = config('hocmai.VQMM_FAHASA.SPECIAL_GIFT');
        $startGoalTime = config('hocmai.VQMM_FAHASA.START_GOAL_SPECIAL_GIFT');
        $endGoalTime   = config('hocmai.VQMM_FAHASA.END_GOAL_SPECIAL_GIFT');
        $now           = time();
        if (!empty($wheelData[$specialGift]) && $now >= strtotime($startGoalTime) && $now <= strtotime($endGoalTime)) {
            arsort($wheelData);
            $sortKey         = array_keys($wheelData);
            $specialGiftRate = $wheelData[$specialGift];
            $largestRate     = $wheelData[$sortKey[0]];

            #tăng tỷ lệ ra quà đặc biệt
            $wheelData[$specialGift] = $largestRate;
            $wheelData[$sortKey[0]]  = $specialGiftRate;
        }

        #7. Random quà
        $wheelData = $this->shuffle_assoc($wheelData);
        $newRandom = new NewRandomHelper();
        $newRandom->setData($wheelData);
        $runWheel = $newRandom->random();
        if (empty($runWheel[0]) || !in_array($runWheel[0], array_keys($wheelData))) {
            return false;
        }

        $giftId   = intval($runWheel[0]);
        $giftHBTP = config('hocmai.VQMM_FAHASA.ARRAY_GIFTS_HBTP');
        $giftHBTP = array_filter(array_map('intval', explode(',', $giftHBTP)));
        if (!in_array($giftId, $giftHBTP)) {
            return $giftId;
        }

        #nếu gift id thuộc các phần quà giới hạn số lượng => check khóa id gift;
        if (\Illuminate\Support\Facades\Cache::has('fahasa_gift_' . $giftId)) {
            $giftId = $this->getWheelResult($wheelData);
        } else {
            #sau 1 phút sẽ mở khóa tránh trùng các quà giới hạn
            \Illuminate\Support\Facades\Cache::put('fahasa_gift_' . $giftId, '1', 60);
        }

        return $giftId;
    }

    public function runWhellFahasa(RunWheelFahasaRequest $request)
    {

        $filter = $request->validated();
        try {
            DB::beginTransaction();
            $ticket = Ticket::query()
                ->where('bill_code', $filter['bill_code'])
                ->first();

            $checkTicket = Ticket::query()
                ->where('bill_code', $filter['bill_code'])
                ->where('status', 'approved')
                ->first();

            $contact = ContactLeadProcess::query()->where('id', $filter['contact_id'])->first();
            if ($ticket->status == 'verified' && empty($checkTicket)) {

                $biasRandom = new BiasRandom();
                $randomGift = RandomGift::where('landing_page_id', $filter['landing_page_id'])
                    ->where('status', 'active')
                    ->get();
//                $sumQuantityRandomGift = RandomGift::where('landing_page_id', $filter['landing_page_id'])
//                    ->where('status', 'active')
//                    ->sum('quantity');
                $randomGiftSort = [];
                $listRandomGift = [];
                foreach ($randomGift as $key => $value) {
                    $randomGiftSort[$value['id']] = $value;
                    $listRandomGift[$value['id']] = $value;
//                    $randomGiftSort[$value['id']]['rate'] = $value['quantity'] / ($sumQuantityRandomGift/100);
//                    $listRandomGift[$value['id']]['rate'] = $value['quantity'] / ($sumQuantityRandomGift/100);
                }
                /** phân chia tỷ lệ theo khu vực và thời gian */
                $configRunWheels = config('hocmai.VQMM_FAHASA');
                $start_date_first = strtotime($configRunWheels['START_TIME_FIRST']);
                $end_date_first = strtotime($configRunWheels['END_TIME_FIRST']);
                $start_date_final = strtotime($configRunWheels['START_TIME_FINAL']);
                $end_date_final = strtotime($configRunWheels['END_TIME_FINAL']);
                $date_now = strtotime(date("Y-m-d"));
                $arrayNotGifts = [];

                if (!empty($start_date_first) && !empty($end_date_first)) {
                    if ($start_date_first <= $date_now && $date_now <= $end_date_first) {
                        $checkGiftHBTP = RandomGiftContact::query()->whereIn('gift_id', explode(',', $configRunWheels['ARRAY_GIFTS_HBTP']))->first();
                        if (!empty($checkGiftHBTP)) {
                            $arrayNotGifts = explode(',', $configRunWheels['ARRAY_GIFTS_HBTP']);
                        } else {
                            if (!empty($configRunWheels['ADDRESS_ONSIDE']) && $ticket->store_name != $configRunWheels['ADDRESS_ONSIDE']) {
                                $arrayNotGifts = explode(',', $configRunWheels['ARRAY_GIFTS_HBTP']);
                            } else {
                                $arrayNotGifts = explode(',', $configRunWheels['SPECIAL_GIFT']);
                            }
                        }
                    } elseif ($start_date_final <= $date_now && $date_now <= $end_date_final) {
                        if (!empty($configRunWheels['ADDRESS_ONSIDE']) && $ticket->store_name != $configRunWheels['ADDRESS_ONSIDE']) {
                            $arrayNotGifts = explode(',', $configRunWheels['SPECIAL_GIFT']);
                        } else {
                            $arrayNotGifts = [];
                        }
                    } else {
                        $arrayNotGifts = explode(',', $configRunWheels['SPECIAL_GIFT']);
                    }
                }
                if (!empty($arrayNotGifts)) {
                    foreach ($arrayNotGifts as $value) {
                        $randomGiftSort[$value]['quantity_use'] = $randomGiftSort[$value]['quantity'];
                    }
                } else {
                    $randomGiftSort = $randomGift;
                }
                /** Setup mảng id các quà được lấy ra để cộng thêm tỉ lệ */
                foreach ($listRandomGift as $key => $value) {
                    if (!empty(in_array($value['id'], $arrayNotGifts)) || $value['quantity'] <= $value['quantity_use']) {
                        unset($listRandomGift[$key]);
                    }
                }

                /** Tính tỉ lệ rate của từng phần thưởng*/
                $data = [];
                $sum_percent = 0;

                foreach ($randomGiftSort as $key => $value) {
                    $sum_quantity_use = (!empty($value['quantity_use'])) ? ($value['rate'] / $value['quantity'] * $value['quantity_use']) : 0;
                    $sum_percent += $sum_quantity_use;
                }
                foreach ($listRandomGift as $key => $value) {
                    $data[$value['id']] = $value['rate'] + $sum_percent / count($listRandomGift);
                }
                $biasRandom->setData($data);
                $giftId = $biasRandom->random();
                $randomGiftSelect = RandomGift::where('id', $giftId[0])->first();
                $randomGiftContact = RandomGiftContact::create([
                    'landing_page_id' => $filter['landing_page_id'],
                    'contact_id' => $filter['contact_id'],
                    'ticket_id' => $ticket->id,
                    'gift_id' => $giftId[0],
                ]);

                if (!empty($contact['email'])) {
                    $couponCode = GiftCoupon::query()
                        ->where('gift_id', $giftId[0])
                        ->where('used', '=', 'no')
                        ->first();

                    $randomGiftContact->coupon_code = !empty($couponCode['code']) ? $couponCode['code'] : '';
                    $randomGiftContact->save();

                    /** Nếu tồn tại coupon code thì cập nhật là đã sữ dụng */
                    if (!empty($couponCode)) {
                        $couponCode->used = 'yes';
                        $couponCode->save();
                    }


                    /** Xử lí template và gửi mail */
                    $emailTemplateCode = 'fahasa_vqmm';

                    $templateEmail = EmailTemplates::where('code', $emailTemplateCode)->first();
                    /** xử lí emailContent */
                    if ($randomGiftSelect['type'] == 'product') {
                        $emailContent = str_replace('{{srcImageGift}}', ('https://hevuichoi.ican.vn/wp-content/uploads/sites/25/2023/05/Email.png'), $templateEmail->content);
                        $randomGiftSelectName = $randomGiftSelect['name'] . ' khi tham gia <span style="color:#000000;font-weight:700;">"Vòng quay may mắn"</span> thuộc chương trình <span style="color:#000000;font-weight:700;">"Hè vui chơi - không rơi kiến thức"</span>';
                    } else {
                        $emailContent = str_replace('{{srcImageGift}}', ('https://hevuichoi.ican.vn/wp-content/uploads/sites/25/2023/05/LayoutEmail.png'), $templateEmail->content);
                        $randomGiftSelectName = $randomGiftSelect['name'];
                    }
                    $emailContent = str_replace('{{full_name}}', ($contact['full_name'] ?? ''), $emailContent);
                    $emailContent = str_replace('{{gift_name}}', ($randomGiftSelectName ?? ''), $emailContent);
                    $emailContent = str_replace('{{description}}', ($randomGiftSelect['description'] ?? ''), $emailContent);
                    $couponCodeTemplate = '<p style="margin-bottom: 0;">Mã code học bổng: <span style="color:#000000;font-weight:700;font-size: 18px;">{{coupon_code}}</span></p>';
                    if (!empty($randomGiftContact['coupon_code'])) {
                        $emailContent = str_replace('{{coupon_code}}', ($randomGiftContact['coupon_code'] ?? ''), $emailContent);
                    } else {
                        $emailContent = str_replace($couponCodeTemplate, '', $emailContent);
                    }

                    $sendEmailParams = [
                        'landing_page_id' => $ticket['landing_page_id'],
                        'contact_lead_process_id' => $ticket['contact_lead_process_id'],
                        'from_email' => 'noreply@hocmai.vn',
                        'from_name' => 'Hệ thống giáo dục HOCMAI',
                        'to_email' => $contact['email'],
                        'to_name' => $contact['full_name'],
                        'subject' => $templateEmail->subject,
                        'content' => $emailContent,
                        'file_attach' => 'nullable|string',
                    ];
                    $email = EmailSave::create($sendEmailParams);
                    SendEmailHocMai::dispatch($email)->delay(Carbon::now()->addSeconds(20));
                }
                //Nếu là quà coupon có email và quà product thì giảm số lượng
                if (!empty($couponCode['code']) || $randomGiftSelect['type'] == 'product') {
                    $randomGiftSelect->quantity_use += 1;
                    $randomGiftSelect->save();
                }
                if (!empty(config('hocmai.ADMIN.is_send'))) {
                    $this->checkQuantityCouponCode($giftId[0], $randomGiftSelect);
                }
                $ticket->status = 'approved';
                $ticket->save();
                DB::commit();
                if (!empty($randomGiftSelect) && $randomGiftSelect['type'] == 'coupon') {
                    $randomGiftSelect = RandomGift::where('id', config('hocmai.VQMM_FAHASA.ID_CBMM_FAHASA'))->first();
                    return response()->json([
                        'message' => 'get success',
                        'status' => 'true',
                        'data' => [
                            'gift' => $randomGiftSelect,
                        ]
                    ]);
                } else {
                    return response()->json([
                        'message' => 'get success',
                        'status' => 'true',
                        'data' => [
                            'gift' => $randomGiftSelect,
                        ]
                    ]);
                }
            } elseif (!empty($checkTicket)) {
                $randomGiftContact = $this->checkGiftCoupon($ticket['id']);
                return response()->json([
                    'message' => 'get success',
                    'status' => 'true',
                    'data' => [
                        'randomGiftContact' => $randomGiftContact,
                    ]
                ]);
            } else {
                return response()->json([
                    'message' => 'Vé chưa được xác thực',
                    'status' => 'fail',
                    'data' => []
                ]);
            }
        } catch (\Throwable $exception) {
//            DB::rollBack();
            return response()->json([
                'data' => [
                    'message' => $exception->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }

    }

    public function checkGiftCoupon($ticket_id)
    {
        $randomGiftCheck = RandomGiftContact::where('ticket_id', $ticket_id)->first();
        $randomGiftSelect = RandomGift::where('id', config('hocmai.VQMM_FAHASA.ID_CBMM_FAHASA'))->first();
        if (empty($randomGiftCheck) || ($randomGiftCheck['type'] == 'coupon')) {
            return $randomGiftSelect;
        } else {
            return $randomGiftCheck;
        }

    }

    public function checkQuantityCouponCode($giftID, $randomGiftSelect)
    {
        $couponCode = GiftCoupon::query()
            ->where('gift_id', $giftID)
            ->where('used', '=', 'no')
            ->get()->toArray();
        $countRandomGift = $randomGiftSelect['quantity'] - $randomGiftSelect['quantity_use'];
        $emailContent = [];
        if ($randomGiftSelect['type'] == 'coupon') {
            if (count($couponCode) <= 200 || $countRandomGift <= 200) {
                $emailContent = '<body><img src="' . $randomGiftSelect['thumb'] . '">
                             <p>Mã coupon cho phần quà <strong>(' . $giftID . ')' . $randomGiftSelect['name'] . '</strong>  sắp hết!</strong><br>
                             <p>Vòng Quay May Mắn FaHaSa</p><br><p>Vui lòng thêm mã vào cơ sở dữ liệu</p>
                             </body>';
            }
        }
        if ($randomGiftSelect['type'] == 'product') {
            if ($countRandomGift <= 15) {
                $emailContent = '<body><img src="' . $randomGiftSelect['thumb'] . '">
                             <p>Phần quà <strong>(' . $giftID . ')' . $randomGiftSelect['name'] . '</strong>  sắp hết!</strong><br>
                             <p>Vòng Quay May Mắn FaHaSa</p><br><p>Vui lòng thêm số lượng vào cơ sở dữ liệu</p>
                             </body>';
            }
        }
        if (!empty($emailContent)) {
            $sendEmailParams = [
                'landing_page_id' => 104,
                'contact_lead_process_id' => 1,
                'from_email' => 'noreply@hocmai.vn',
                'from_name' => 'HOCMAI',
                'to_email' => config('hocmai.ADMIN.email'),
                'to_name' => config('hocmai.ADMIN.full_name'),
                'subject' => 'THÔNG BÁO MÃ COUPON_CODE VQMM FAHASA SẮP HẾT',
                'content' => $emailContent,
                'file_attach' => 'nullable|string',
            ];
            $email = EmailSave::create($sendEmailParams);
            SendEmailHocMai::dispatch($email);
        }
    }

    public function runWheel(Request $request)
    {
        $userId = $request->user_id;
        $landingPageId = $request->landing_page_id;
        $ticketId = $request->ticket_id;
        $couponCode = $request->coupon_code;


        /***
         * Kiểm tra điều kiện quay (Đã mua hàng hay chưa)
         *
         */
        $buyData = $this->buyRepository->getPackageComboPackageInTime($userId,
            Carbon::createFromFormat('Y-m-d H:i:s', '2022-04-18 00:00:00')->timestamp,
            Carbon::createFromFormat('Y-m-d H:i:s', '2022-05-06 00:00:00')->timestamp);

        /***
         * trường hợp có gói tặng trong thời gian
         */
        $hasGift = false;
        /***
         * trường hợp đã mua gói trong thời gian
         */
        $hasBuy = false;

        foreach ($buyData->data as $eachCheck) {
            if ($eachCheck) {
                if ($eachCheck->gift) {
                    $hasGift = true;
                } else {
                    $hasBuy = true;
                }
            }
        }

        /***
         * Trường hợp là test user thì không cần check mua gói
         */
        if ((!$hasBuy && !$hasGift) && !$this->testUser($userId)) {
            /**
             * không cho quay nếu không đủ điều kiện được tặng gói hoặc gói đã
             */
            return response()->json([
                'message' => 'get success',
                'code' => 'user_not_buy_in_time',
                'data' => [
                    'gift' => null,
                ]
            ]);
        }

        $checkBefore = RandomGiftContact::where('user_id', $userId)->where('landing_page_id', $landingPageId)->first();

        if ($checkBefore) {
            /***
             * nếu đã quay rồi thì trả về kết quả cũ
             */


            /***
             * check info in contact nếu đã để lại info thì báo hết lượt quay nếu quay rồi nhưng chưa để lại info thì quay lại kèm kết quả cũ
             */
            $contactCheck = ContactLeadProcess::where('landing_page_id', $landingPageId)->where('user_id', $userId)->first();

            if ($contactCheck) {
                return response()->json([
                    'message' => 'get success',
                    'code' => 'user_has_taken',
                    'data' => [
                        'gift' => [
                            'id' => $checkBefore->gift->id,
                            'name' => $checkBefore->gift->name,
                        ],
                    ]
                ]);
            } else {
                return response()->json([
                    'message' => 'get success',
                    'code' => 'user_has_taken_and_not_info',
                    'data' => [
                        'gift' => [
                            'id' => $checkBefore->gift->id,
                            'name' => $checkBefore->gift->name,
                        ],
                    ]
                ]);
            }

        } else {
            /***
             * chưa quay thì quay
             */
            $listGift = RandomGift::all();

            $wheel = new Wheel(
                $listGift->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $item->quantity - $item->quantity_use,
                    ];
                }),
                $listGift->first(function ($item) {
                    return $item->is_default == 1;
                })
            );
            /***
             * Nếu chưa mua mà chỉ có gói tặng thì bắt buộc rơi vào item default
             */
            if (!$hasBuy && $hasGift) {
                $wheel->forceDefault(true);
            }

            $results = [];

            $result = $wheel->getResult();

            RandomGiftContact::create([
                'landing_page_id' => $landingPageId,
                'contact_id' => 1,
                'gift_id' => $result['id'],
                'user_id' => $userId,
                'ticket_id' => $ticketId,
                'coupon_code' => $couponCode,
                'created_by' => 0,
                'updated_by' => 0,
            ]);
            $gift = RandomGift::find($result['id']);
            $gift->quantity_use++;
            $gift->save();

            return response()->json([
                'message' => 'get success',
                'code' => 'run_success',
                'data' => [
                    'gift' => $result,
                ]
            ]);
        }


    }
}
