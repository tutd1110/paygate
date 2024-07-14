<?php

namespace App\Http\Requests\ContactExamLog;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class ListContactExamLog extends FormRequest
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
            'contact_lead_process_id' => 'required|integer',
            'session_id' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'contact_lead_process_id.required' => "Mã thí sinh không được trống!",
            'contact_lead_process_id.integer'=> "Mã thí sinh phải là số!",
            'session_id.required' => "Session_id không được trống!",
        ];
    }
}
