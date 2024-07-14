<?php

namespace App\Http\Requests\LandingPage;

use App\Http\Requests\ProcessNullValidate;
use App\Http\Requests\ValidateJsonResponse;
use App\Models\ApiPartner;
use App\Models\LandingPage;
use Illuminate\Foundation\Http\FormRequest;

class LandingPageRequest extends FormRequest
{

    use ValidateJsonResponse;

    use ProcessNullValidate;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function myValidate()
    {
        $codeIgnore = '';

        if ($this->method() == 'PUT' || $this->method() == 'PATCH') {
            $codeIgnore = ','.$this->landing_page;
        }


        return [
            'code' => 'required|string|unique:'.(new LandingPage())->getTable().',code'.$codeIgnore,
            'site_id' => 'integer|nullable',
            'department_id' => 'integer',
            'event' => 'nullable|string',
            'domain_name' => 'required|nullable|string',
            'description' => 'nullable|string',
            'server_ip' => 'nullable|string',
            'start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s',
            'status' => 'string|in:new,processing,waiting,approved,expired|nullable',
            'developer' => 'nullable|string',
            'type' => 'nullable|string|in:basic,advanced,payment',
            'purpose' => 'nullable|string|in:contact,learn,reserve,payment,gift,game,other',
            'start_time_coupon' => 'nullable|date_format:Y-m-d H:i:s',
            'end_time_coupon' => 'nullable|date_format:Y-m-d H:i:s',
            'allow_reserve_start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'register_start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'register_end_time' => 'nullable|date_format:Y-m-d H:i:s',
            'olm_id' => 'integer|nullable',
            'api_info' => 'string|nullable',
            'hotline' => 'string|nullable',
            'send_sms_invoice_delay' => 'nullable|integer|min:0|max:99999999',
            'push_crm_invoice_delay' => 'nullable|integer|min:0|max:99999999',
            'created_by' => 'integer|nullable',
            'updated_by' => 'integer|nullable',
            'campaign_ids.*' => 'integer',
            'partner_ids' => 'nullable|array',
            'partner_ids.*' => 'nullable|integer|exists:'.(new ApiPartner())->getTable().',id',
            'transfer_syntax' => 'string|max:255|nullable',
            'is_send_sms_paid' => 'string|nullable',
            'auto_cancel_order' => 'integer|nullable',
            'verifiy_type' => 'string|nullable',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->processBeforeUpdate($this->myValidate());
    }

    public function messages()
    {
        return [
            'partner_ids.*.exists' => 'partner_id not exist'
        ];
    }

}
