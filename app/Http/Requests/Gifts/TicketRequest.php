<?php

namespace App\Http\Requests\Gifts;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    use ValidateJsonResponse;

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
        if ($this->method() == 'POST') {
            return [
                'landing_page_id' =>'nullable|integer',
                'contact_lead_process_id' => 'required|integer',
                'bill_code' => 'required|string',
                'bill_value' => 'required|integer',
                'store_name' => 'required|string',
                'status' => 'in:new,approved',
            ];
        } elseif ($this->method() == 'PUT') {
            return [
                'landing_page_id' =>'nullable|integer',
                'contact_lead_process_id' => 'nullable|integer',
                'bill_code' => 'nullable|string',
                'bill_value' => 'nullable|integer',
                'store_name' => 'nullable|string',
                'status' => 'in:new,verified',
            ];
        }
    }
}
