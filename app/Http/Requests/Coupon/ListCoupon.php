<?php

namespace App\Http\Requests\Coupon;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class ListCoupon extends FormRequest
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
            'landing_page_id' => 'required|integer|exists:landingpages,id',
            'page' => 'integer|nullable',
            'is_used' => 'integer|nullable|in:0,1',
            'begin_time' => 'nullable|date_format:Y-m-d',
            'end_time' => 'nullable|date_format:Y-m-d',
            'begin_time_use' => 'nullable|date_format:Y-m-d',
            'end_time_use' => 'nullable|date_format:Y-m-d',
        ];
    }
}
