<?php

namespace App\Http\Requests\V2\SmsReserve;

use App\Http\Requests\ValidateJsonResponse;
use App\Lib\FormatPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class SmsReserveOnSendRequest extends FormRequest
{
    use ValidateJsonResponse;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function validationData()
    {
        $data = parent::validationData();

        if (isset($data['phone'])) {
            $data['phone'] = FormatPhoneNumber::toBasic($data['phone']);
        }
        if (isset($data['send_phone'])) {
            $data['send_phone'] = FormatPhoneNumber::toBasic($data['send_phone']);
        }

        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sms_content' => 'nullable|string',
            'coupon_code' => 'required',
            'event' => 'required',
            'send_phone' => 'required',
        ];
    }
}
