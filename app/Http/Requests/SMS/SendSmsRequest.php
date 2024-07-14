<?php

namespace App\Http\Requests\SMS;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class SendSmsRequest extends FormRequest
{
    use ValidateJsonResponse;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
       return [
           'phone' => 'required|numeric',
           'sms_content' => 'required',
           'contact_lead_process_id' => 'required|integer',
           ];
    }
}
