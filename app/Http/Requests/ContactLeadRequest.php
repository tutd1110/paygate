<?php

namespace App\Http\Requests;

use App\Lib\FormatPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class ContactLeadRequest extends FormRequest
{
    use ProcessNullValidate;
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

        return $data;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->processBeforeUpdate([
            'user_id' => 'nullable|integer',
            'landing_page_id' => 'required|integer',
            'campaign_id' => 'nullable|integer',
            'crm_id' => 'nullable|integer',
            'olm_id' => 'nullable|integer',
            'sashi_id' => 'nullable|integer',
            'full_name' => 'required|string',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'email' => 'nullable|string|email',
            'class' => 'nullable|integer',
            'action' => 'nullable|string',
            'action_status' => 'nullable|integer',
            'crm_type' => 'string|nullable',
            'description' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
            'utm_source' => 'nullable|string',
            'utm_medium' => 'nullable|string',
            'is_active' => 'nullable|integer',
            'register_ip' => 'nullable|string',
            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
            'session_id' => 'nullable|string',
            'line' => 'nullable|string',
            'gender'=>'nullable|string',
            'birth_day'=>'nullable|string',
            'verified'=>'nullable|in:yes,no',
            'typeLDP' => 'nullable|integer',
            'idSchool' => 'nullable|integer',
            'agentCode' => 'nullable|integer',
        ]);
    }
}
