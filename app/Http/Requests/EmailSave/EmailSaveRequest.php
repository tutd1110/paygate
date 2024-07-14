<?php

namespace App\Http\Requests\EmailSave;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class EmailSaveRequest extends FormRequest
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

    public function validationData()
    {
        $data = parent::validationData();

        if (isset($data['cc_email']) && !is_array($data['cc_email'])) {
            $data['cc_email'] = [$data['cc_email']];
        }

        if (isset($data['bcc_email']) && !is_array($data['bcc_email'])) {
            $data['bcc_email'] = [$data['bcc_email']];
        }

        $data['from_email'] =  $data['from_email'] ?? 'noreply@hocmai.vn';
        $data['from_name'] =  $data['from_name'] ?? 'Hệ thống giáo dục HOCMAI';

        return $data;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'landing_page_id' => 'required|integer',
            'contact_lead_process_id' => 'required|integer',
            'from_email' => 'required|string|email',
            'from_name' => 'required|string',
            'to_email' => 'required|string|email',
            'to_name' => 'required|string',
            'cc_email.*' => 'nullable|string|email',
            'bcc_email.*' => 'nullable|string|email',
            'reply_to' => 'nullable|string|email',
            'subject' => 'required|string|max:255',
            'content' => 'required|max:65000',
            'file_attach' => 'nullable|string',
            'send_time' => 'nullable|date_format:Y-m-d H:i:s',
            'status' => 'nullable|string|in:waiting,sent',
        ];
    }

}
