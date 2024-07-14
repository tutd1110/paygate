<?php

namespace App\Http\Requests\SYS;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class SysModuleRequest extends FormRequest
{
    use ValidateJsonResponse;
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'module' => 'nullable|string',
            'module_alias' =>'nullable|string',
            'created_by' => 'integer|nullable',
            'updated_by' => 'nullable|integer',
        ];

    }
}
