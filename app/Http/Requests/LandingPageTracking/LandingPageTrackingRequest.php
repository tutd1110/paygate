<?php

namespace App\Http\Requests\LandingPageTracking;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class LandingPageTrackingRequest extends FormRequest
{
    use ValidateJsonResponse;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        if ($this->method() == 'POST') {
            return [
                'landing_page_id' => 'required|integer',
                'header_bottom' => 'nullable',
                'body' => 'nullable',
                'body_bottom' => 'nullable',
                'footer' => 'nullable',
                'created_by' => 'integer|nullable',
                'updated_by' => 'integer|nullable',
            ];
        } elseif ($this->method() == 'PUT') {
            return [
                'header_bottom' => 'nullable',
                'body' => 'nullable',
                'body_bottom' => 'nullable',
                'footer' => 'nullable',
                'created_by' => 'integer|nullable',
                'updated_by' => 'integer|nullable',
            ];
        }
    }
}
