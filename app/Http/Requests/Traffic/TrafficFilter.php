<?php

namespace App\Http\Requests\Traffic;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class TrafficFilter extends FormRequest
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
            'landing_page_id' => 'nullable|integer',
            'utm_campaign' => 'string|nullable',
            'utm_medium' => 'string|nullable',
            'utm_source' => 'string|nullable',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'group_by' => 'nullable|in:landing_page_id,user_id,campaign_id,cookie_id,session_id,uri,query_string,utm_medium,utm_source,utm_campaign,register_ip',
            'order' => 'nullable|in:landing_page_id,user_id,campaign_id,cookie_id,session_id,uri,query_string,utm_medium,utm_source,utm_campaign,register_ip',
            'direction' => 'nullable|in:asc,desc'
        ];

        $canArrayKey = [
            'landing_page_id',
            'utm_campaign',
            'utm_medium',
            'utm_source',
            'order',
            'group_by',
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
