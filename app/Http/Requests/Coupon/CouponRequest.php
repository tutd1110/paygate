<?php

namespace App\Http\Requests\Coupon;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'landing_page_id' => 'required|integer',
            'user_id' => 'required|integer',
            'pre_start_time' => 'required|integer',
            'allow_reserve_start_time' => 'required|integer',
            'start_time_1' => 'required|integer',
            'start_time_2' => 'required|integer',
            'end_time_1' => 'required|integer',
            'end_time_2' => 'required|integer',
            'reserve_fee' => 'required|integer',
            'start_coupon' => 'required|integer',
            'end_coupon' => 'required|integer',
            'combo' => 'required|string',
            'package_group_name' => 'required|string',
            'discount' => 'required|integer',
            'utm_campaign' => 'nullable|string',
            'utm_source' => 'nullable|string',
            'session_id' => 'required|string',
            'fsuid' => 'required|string',
            'uri' => 'nullable|string',
            'email_subject' => 'required|string',
            'email_content' => 'required|string',
        ];
    }
}
