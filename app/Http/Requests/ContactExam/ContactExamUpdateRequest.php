<?php

namespace App\Http\Requests\ContactExam;

use Illuminate\Foundation\Http\FormRequest;

class ContactExamUpdateRequest extends FormRequest
{
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
            'total_question' => 'required|integer',
            'total_score' => 'required|integer',
            'is_done' => 'required',
            'total_time' => 'integer',
            'number' => 'integer',
        ];
    }
}
