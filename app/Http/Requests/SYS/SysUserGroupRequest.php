<?php

namespace App\Http\Requests\SYS;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class SysUserGroupRequest extends FormRequest
{
    use ValidateJsonResponse;
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_group' => 'array|required',
            'user_group.*.user_id' => 'integer|nullable',
            'user_group.*.group_id' => 'integer|nullable',
            'created_by' => 'integer|nullable',
            'updated_by' => 'nullable|integer',
        ];
    }
}
