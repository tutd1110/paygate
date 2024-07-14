<?php

namespace App\Http\Requests\SYS;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class SysGroupPermissionRequest extends FormRequest
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
                'group_permission' => 'array|required',
                'group_permission.*.group_id' => 'integer|nullable',
                'group_permission.*.permission_id' => 'integer|nullable',
                'created_by' => 'integer|nullable',
                'updated_by' => 'nullable|integer',
            ];
        } elseif ($this->method() == 'PUT') {
            return [
                'group_id' => 'nullable|string|max:50',
                'permission_id' => 'nullable|string',
                'created_by' => 'integer|nullable',
                'updated_by' => 'nullable|integer',
            ];
        } elseif ($this->method() == 'DELETE') {
            return [
                'group_permission' => 'array|required',
                'group_permission.*.group_id' => 'integer|nullable',
                'group_permission.*.permission_id' => 'integer|nullable',
            ];
        }
    }
}
