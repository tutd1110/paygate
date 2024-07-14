<?php

namespace App\Http\Requests\SYS;

use App\Http\Requests\ValidateJsonResponse;
use App\Models\User;
use Dotenv\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SysUserRequest extends FormRequest
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
                'partner_code' => 'nullable|string',
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|numeric',
                'address' => 'string|nullable|max:255',
                'owner' => 'in:yes,no',
                'status' => 'in:active,inactive,deleted',
                'created_by' => 'integer|nullable',
                'updated_by' => 'nullable|integer',
            ];
        } elseif ($this->method() == 'PUT') {
            return [
                'partner_code' => 'nullable|string',
                'name' => 'string',
                'phone' => 'nullable|numeric',
                'address' => 'string|nullable|max:255',
                'owner' => 'in:yes,no',
                'status' => 'in:active,inactive,deleted',
                'created_by' => 'integer|nullable',
                'updated_by' => 'nullable|integer',
            ];
        }
    }
}
