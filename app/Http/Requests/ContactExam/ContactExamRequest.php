<?php

namespace App\Http\Requests\ContactExam;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class ContactExamRequest extends FormRequest
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
        return [
            'contact_lead_process_id' => 'required|integer',
            'session_id' => 'required|string',
            'total_question' => 'required|integer',
            'total_score' => 'required|integer',
            'is_done' => 'required',
            'total_time' => 'integer',
        ];
    }
    public function messages()
    {
        return [
            'contact_lead_process_id.required' => "Mã thí sinh không được trống!",
            'contact_lead_process_id.integer'=> "Mã thí sinh phải là số!",
            'session_id.required' => "Session_id không được trống!",
            'total_question.required' => "Tổng số câu hỏi không được trống",
            'total_question.integer' => "Tổng số câu hỏi phải là dạng số",
            'total_score.required' => "Tổng điểm không được trống",
            'total_score.integer' => "Tổng điểm phải là dạng số",
            'is_done.required' => "Trạng thái nộp bài không được để trống",
            'total_time.integer' => 'Tổng thời gian phải là kiểu số',
        ];
    }
}
