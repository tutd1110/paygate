<?php

namespace App\Http\Requests\SYS;

use Illuminate\Foundation\Http\FormRequest;

class SysUserLandingpageRequest extends FormRequest
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
            'user_landingpage' => 'array|required',
            'user_landingpage.*.user_id' => 'integer|nullable',
            'user_landingpage.*.landing_page_id' => 'integer|nullable',
            'created_by' => 'integer|nullable',
            'updated_at' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
        ];
    }
}
