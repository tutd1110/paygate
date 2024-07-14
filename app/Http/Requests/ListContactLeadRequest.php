<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListContactLeadRequest extends FormRequest
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
        $validate = [
            'id' => 'nullable|integer',
            'user_id' => 'nullable|integer',
            'landing_page_id' => 'nullable|integer',
            'campaign_id' => 'nullable|integer',
            'crm_id' => 'nullable|integer',
            'olm_id' => 'nullable|integer',
            'sashi_id' => 'nullable|integer',
            'fullname' => 'string|nullable',
            'phone' => 'string|nullable|max:20',
            'email' => 'email|nullable',
            'class' => 'nullable|integer',
            'register_ip' => 'nullable|integer',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'order' => 'nullable|in:id,user_id,landing_page_id,is_has_from_reserve_form,campaign_id,crm_id,sashi_id,olm_id,full_name,phone,email,class,description,scan,utm_medium,utm_source,utm_campaign,is_duplicate,is_email_duplicate,is_phone_duplicate,is_active,register_ip',
            'direction' => 'nullable|in:asc,desc'
        ];

        $canArrayKey = [
            'id',
            'user_id',
            'landing_page_id',
            'campaign_id',
            'crm_id',
            'olm_id',
            'sashi_id',
            'fullname',
            'phone',
            'email',
            'class',
            'register_ip',
            'order',
            'direction',
        ];

        $data = $this->all();

        foreach ($canArrayKey as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                $validate[$key.'.*'] = $validate[$key];
                $validate[$key] = 'array';
            }
        }

        return $validate;
    }
}
