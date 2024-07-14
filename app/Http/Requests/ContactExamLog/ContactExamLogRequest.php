<?php

namespace App\Http\Requests\ContactExamLog;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class ContactExamLogRequest extends FormRequest
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
            'question_id' => 'required|integer',
            'question_name' => 'required|string|max:500',
            'result' => 'string|nullable',
            'score' => 'integer|nullable',
            'time' => 'integer',
        ];
    }

    public function messages()
    {
        return [
            'contact_lead_process_id.required' => "Mã thí sinh không được trống!",
            'contact_lead_process_id.integer'=> "Mã thí sinh phải là số!",
            'session_id.required' => "Session_id không được trống!",
            'question_id.required' => "ID câu hỏi không được trống",
            'question_id.integer' => "ID câu hỏi phải là dạng số",
            'question_name.required' => "Tên câu hỏi không được trống",
            'question_name.max' => "Tên câu hỏi không quá 500 kí tự",
            'result.string' => "Câu trả lời phải là dạng văn bản",
            'score.integer' => "Điểm phải là dạng số",
            'time.integer' => 'Thời gian phải là kiểu số',
        ];
    }
}
