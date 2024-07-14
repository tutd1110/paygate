<?php

namespace App\Http\Requests\Traffic;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class TrafficRequest extends FormRequest
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
            'landing_page_id' => 'required',
            'session_id' => 'required|string|max:191',
            'cookie_id' => 'nullable|string|max:65000',
            'utm_medium' => 'nullable|string|max:191',
            'utm_source' => 'nullable|string|max:191',
            'utm_campaign' => 'nullable|string|max:191',
            'register_ip' => 'nullable|string|max:191',
        ];
    }
}
