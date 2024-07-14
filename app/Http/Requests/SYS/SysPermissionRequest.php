<?php

namespace App\Http\Requests\SYS;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class SysPermissionRequest extends FormRequest
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
                'module_id' => 'required|integer',
                'name' => 'required|string',
                'name_alias' => 'nullable|string',
                'router' => 'required|string',
                'created_by' => 'integer|nullable',
                'updated_by' => 'nullable|integer',
            ];
        } elseif ($this->method() == 'PUT') {
            return [
                'module_id' => 'nullable|integer',
                'name' => 'nullable|string',
                'name_alias' => 'nullable|string',
                'router' => 'nullable|string',
                'created_by' => 'integer|nullable',
                'updated_by' => 'nullable|integer',
            ];
        }
    }
}
