<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceList extends FormRequest
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
            'phone' => 'nullable|string',
            'user_id' => 'nullable|integer',
            'landing_page_id' => 'nullable|integer',
            'contact_lead_process_id' => 'integer|nullable',
            'status' => 'nullable|string|in:new,processing,paid',
            'limit' => 'nullable|integer',
            'order' => 'nullable|in:id,code,landing_page_id,user_id,contact_lead_process_id,amount,discount,voucher_code,quantity,status,created_at,updated_at',
            'direction' => 'nullable|in:asc,desc',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ];

        $canArrayKey = [
            'id',
            'landing_page_id',
            'code',
            'user_id',
            'contact_lead_process_id',
            'status',
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
