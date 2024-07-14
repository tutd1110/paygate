<?php

namespace App\Http\Requests\SMSTemplate;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class SMSTemplateRequest extends FormRequest
{
    use ValidateJsonResponse;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        if ($this->method() == 'PUT') {
            return [
                'template_name' => 'nullable|string',
                'code' => 'nullable|string',
                'event' => 'nullable|string',
                'status' => 'in:active,inactive',
                'bind_param' => 'string|nullable',
                'landing_page_id' => 'integer',
            ];
        } elseif ($this->method() == 'POST') {
            return [
                'template_name' => 'required|string',
//                'parent_id' => 'required|integer',
                'code' => 'required|string',
                'event' => 'required|string',
                'status' => 'in:active,inactive',
                'content' => 'required',
                'bind_param' => 'string|nullable',
                'landing_page_id' => 'integer',
            ];
        }
    }
}
