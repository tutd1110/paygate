<?php

namespace App\Http\Requests\SYS;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class SysGroupRequest extends FormRequest
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
                'partner_code' => 'required|string|max:50',
                'name' => 'required|string',
                'description' => 'nullable|string',
                'status' => 'in:active,inactive',
                'created_by' => 'integer|nullable',
                'updated_by' => 'nullable|integer',
            ];
        } elseif ($this->method() == 'PUT') {
            return [
                'partner_code' => 'nullable|string|max:50',
                'name' => 'nullable|string',
                'description' => 'nullable|string',
                'status' => 'in:active,inactive',
                'created_by' => 'integer|nullable',
                'updated_by' => 'nullable|integer',
            ];
        }
    }
}
