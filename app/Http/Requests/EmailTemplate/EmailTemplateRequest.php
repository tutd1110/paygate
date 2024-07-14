<?php

namespace App\Http\Requests\EmailTemplate;
use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class EmailTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
            return [
                'code' => 'required|string',
                'name' => 'required|string',
                'subject' => 'required|string',
                'content' => 'required|string',
                'attachment_files' => 'nullable|string',
                'content' => 'required|string',
                'description' => 'nullable|string',
                'status' => 'in:active,inactive',
            ];
    }
}
