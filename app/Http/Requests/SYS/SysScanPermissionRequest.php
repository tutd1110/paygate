<?php

namespace App\Http\Requests\SYS;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class SysScanPermissionRequest extends FormRequest
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
            'listRoutes' => 'required|array',
            'nameModules' => 'required|array',
            'listRoutes.*' => 'string|nullable',
            'nameModules.*' => 'string|nullable',
            'session_id'=> 'integer|nullable'
        ];
    }
}
