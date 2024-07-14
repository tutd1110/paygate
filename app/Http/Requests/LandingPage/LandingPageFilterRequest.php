<?php

namespace App\Http\Requests\LandingPage;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class LandingPageFilterRequest extends FormRequest
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
            'code' => 'nullable|string',
            'site_id' => 'nullable|integer',
            'domain_name' => 'string|nullable',
            'status' => 'nullable|string|in:new,processing,waiting,approved,expired',
            'server_ip' => 'nullable|string',
            'limit' => 'nullable|integer',
            'order' => 'nullable|in:id,site_id,code ,department_id,domain_name ,description ,server_ip,start_time,end_time,status ,developer ,type ,api_info ,created_by,updated_by',
            'direction' => 'nullable|in:asc,desc'
        ];

        $canArrayKey = [
            'id',
            'code',
            'site_id',
            'domain_name',
            'server_ip',
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
