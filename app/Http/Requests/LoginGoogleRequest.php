<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginGoogleRequest extends FormRequest
{
    use ValidateJsonResponse;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'=> 'required|string',
            'email' => 'required|unique:users,email|email|string',
            'phone' => 'nullable|max:30|integer',
            'profile_photo_path' => 'nullable|string|max:2048',
            'google_id' => 'required|string|max:255',
            'is_admin'=>'nullable|integer'
        ];
    }
}
