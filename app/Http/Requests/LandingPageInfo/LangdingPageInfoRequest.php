<?php

namespace App\Http\Requests\LandingPageInfo;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class LangdingPageInfoRequest extends FormRequest
{
    use ValidateJsonResponse;

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'landing_page_id' => 'bail|required|integer',
            'transfer_syntax' => 'bail|string|max:255|nullable',
            'sms_content_paid' => 'bail|string|nullable|max:255',
            'sms_content_remind' => 'bail|string|nullable|max:255',
            'email_content_paid' => 'bail|string|nullable',
            'email_content_remind' => 'bail|string|nullable',
            'updated_by' => 'bail|integer',
            'created_by' =>'bail|integer'
        ];
    }
}
