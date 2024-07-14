<?php

namespace App\Repositories\Contact;

use App\Helper\DefaultValue;
use App\Models\ContactLead;
use App\Models\ContactLeadProcess;
use App\Models\ContactLeadProcessReserveLog;
use App\Models\LandingPage;
use App\Models\Gifts\RandomGiftContact;
use Carbon\Carbon;

class ContactEloquentRepository implements ContactRepositoryInterface
{

    /****
     * @var ContactLeadProcess|mixed
     */
    private $contactLeadProcessModel;

    /***
     * @var ContactLeadProcessReserveLog
     */
    private $contactLeadProcessReserveLogModel;

    public function __construct()
    {
        $this->contactLeadProcessModel = app(ContactLeadProcess::class);
        $this->contactLeadProcessReserveLogModel = app(ContactLeadProcessReserveLog::class);
    }

    public function process(ContactLead $contactLead)
    {
        $contactLeadProcess = null;
        /****
         * kiểm tra xem có phải contact này cùng ip có trùng với sdt và ip trước đó không
         * B1: Lấy contact và campaign và số điện thoại để xử lý
         */
        $listExistContact = $this->contactLeadProcessModel
            ->where('phone', $contactLead->phone)
            ->where('campaign_id', $contactLead->campaign_id ?? 0)
            ->where('landing_page_id', $contactLead->landing_page_id ?? 0)
            ->where('user_id', $contactLead->user_id ?? 0)
            ->where('is_first_contact', 1)
            ->orderBy('id', 'asc')->get();

        $landingPage = $contactLead->landingPage;

        /***
         * Trường hợp nếu là landing page tặng thì bỏ qua check luật mà vẫn thêm contact
         */
        if ($landingPage && $landingPage->purpose == 'gift') {


            $data = $contactLead->toArray();
            $data['contact_lead_id'] = $contactLead->id;
            $data['is_first_contact'] = 1;

            /****
             * Kiểm tra quà tặng của contact thêm 1 lần nữa để xem có đúng với quà quay được không.
             */

            $giftContact = RandomGiftContact::where('landing_page_id', $contactLead->landing_page_id)
                ->where('user_id', $contactLead->user_id)->first();

            /***
             * Thêm vào gift quà được tặng
             */
            $data['description'] = $contactLead->description."__ Quà xác nhận quay được:".($giftContact->gift->name ??
                    '');


            $contactLeadProcess = $this->contactLeadProcessModel->create($data);

            /***
             * Xong tất cả đánh dấu là đã scan
             */
            $contactLead->scan = 1;
            $contactLead->save();

            return $contactLeadProcess;
        }
        if ($listExistContact->count()) {
            /***
             * kiểm tra từ ngày contact đầu tiên vào đến giờ đã quá 7 ngày hay chưa nếu  quá rồi thì tạo mới và
             * đánh dấu là contact mới và bỏ tính contact mới đó là ngày đầu tiên
             */
            if (Carbon::createFromFormat('Y-m-d H:i:s', $listExistContact->first()->updated_at)
                    ->diffInMinutes(Carbon::now()) > 5) {
                /***
                 * Đã quá 7 ngày thì không còn first contact nữa
                 */
                foreach ($listExistContact as $value) {
                    $value->is_first_contact = 0;
                    $value->save();
                }

                /***
                 * tạo mới sau khi cập nhật lại first contact của đống cũ
                 */
                $data = $contactLead->toArray();
                $data['contact_lead_id'] = $contactLead->id;
                $data['is_first_contact'] = 1;
                $contactLeadProcess = $this->contactLeadProcessModel->create($data);
            } else {

                /***
                 * Cập nhật lại thông tin của contact
                 */
                foreach ($listExistContact as $value) {
                    $updateExistContactData = $contactLead->toArray();
                    if (isset($updateExistContactData['landing_page_id'])) {
                        /***
                         * chỉ cập nhật thông tin không cập nhật landing page id
                         */
                        unset($updateExistContactData['landing_page_id']);
                    }
                    $value->fill($updateExistContactData);
                    $value->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $value->save();
                }

                /****
                 * Trường hợp chưa quá 7 ngày thì lấy luôn contact đã được thêm lần trước để sử dụng
                 */
                $contactLeadProcess = $listExistContact->first();
            }


        } else {
            /****
             * Chưa tồn tại thì tính vào contact mới và tính contact là contact đầu tiên
             */
            $data = $contactLead->toArray();
            $data['contact_lead_id'] = $contactLead->id;
            $data['is_first_contact'] = 1;

            $contactLeadProcess = $this->contactLeadProcessModel->create($data);
        }

        /***
         * Xong tất cả đánh dấu là đã scan
         */
        $contactLead->scan = 1;
        $contactLead->save();

        return $contactLeadProcess;
    }

    public function addReserveLogFromLanding(ContactLeadProcess $contactLeadProcess, array $extendData = [])
    {
        $landingPage = $contactLeadProcess->landingPage;
        $checkExistLog = $this->contactLeadProcessReserveLogModel->where('contact_lead_process_id',
            $contactLeadProcess->id)->where('event', $landingPage->event)->first();

        if ($checkExistLog) {
            return $checkExistLog;
        }

        $newLog = $this->contactLeadProcessReserveLogModel->create([
            'contact_lead_process_id' => $contactLeadProcess->id,
            'coupon_code' => '',
            'phone' => $contactLeadProcess->phone,
            'send_phone' => $contactLeadProcess->phone,
            'event' => $landingPage->event ?? '',
            'status' => 'create',
            'line' => $extendData['line'] ?? '',
            'is_crm_pushed' => $extendData['is_crm_pushed'] ?? 0,
            'is_has_from_reserve_form' => 1,
            'crm_pushed_at' => $extendData['crm_pushed_at'] ?? null,
            'landing_page_id' => $extendData['landing_page_id'] ?? null,
        ]);

        return $newLog;
    }

    /****
     * @param $phone
     * @param $sendPhone
     * @param $event
     * @param $couponCode
     */
    public function processOnReceiveSms($sendPhone, $event, $couponCode, $smsContent = '')
    {
        $checkLog = $this->contactLeadProcessReserveLogModel
            ->where('phone', $sendPhone)
            ->where('event', $event)
            ->first();

        if ($checkLog) {
            /***
             * Có rồi đổi trạng thái log sang sent_sms_reserve
             */
            $checkLog->status = 'sent_sms_reserve';
            $checkLog->coupon_code = $couponCode;
            $checkLog->sms_content = $smsContent ?? '';
            $checkLog->save();

            return $checkLog;

        } else {
            /***
             * Trường hợp không có log thì tạo log
             * Đánh dấu contact này không phải đến từ form đặt chỗ
             * is_has_from_reserve_form = 0
             */

            $landingPageDefault = LandingPage::where('event', $event)->where('default', 1)->first();

            /***
             * Tạo contact
             */
            $contact = ContactLead::create([
                'landing_page_id' => $landingPageDefault->id ?? DefaultValue::LANDING_PAGE_DEFAULT_ID,
                'phone' => $sendPhone,
                'is_has_from_reserve_form' => 0,
            ]);

            /***
             * tạo contact sau khi xử lý
             */
            $contactProcess = $this->process($contact);

            $newLog = $this->contactLeadProcessReserveLogModel->create([
                'contact_lead_process_id' => $contactProcess->id,
                'coupon_code' => $couponCode,
                'phone' => $sendPhone,
                'send_phone' => $sendPhone,
                'event' => $event,
                'status' => 'sent_sms_reserve',
                'sms_content' => $smsContent ?? '',
                'is_crm_pushed' => 0,
                'crm_pushed_at' => null,
                'is_has_from_reserve_form' => 0,
            ]);

            return $newLog;
        }
    }


}
