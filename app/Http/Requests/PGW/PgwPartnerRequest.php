<?php

namespace App\Http\Requests\PGW;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class PgwPartnerRequest extends FormRequest
{
    use ValidateJsonResponse;

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
        if (strtoupper($this->method()) == 'POST') {
            return [
                'code' => 'required|unique:pgw_partners,code|string|max:25',
                'name' => 'required|string',
                'status' => 'in:active,inactive',
                'description' => 'nullable|string',
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'created_at' => ' nullable',
                'updated_at' => 'nullable',
            ];
        } elseif (strtoupper($this->method()) == 'PUT') {
            return
                [
                    'name' => 'required|string',
                    'status' => 'in:active,inactive',
                    'description' => 'nullable|string',
                    'created_by' => 'nullable|integer',
                    'updated_by' => 'nullable|integer',
                    'created_at' => ' nullable',
                    'updated_at' => 'nullable',
                ];
        }
    }
}
