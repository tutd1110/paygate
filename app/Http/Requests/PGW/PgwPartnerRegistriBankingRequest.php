<?php

namespace App\Http\Requests\PGW;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class PgwPartnerRegistriBankingRequest extends FormRequest
{
    use ValidateJsonResponse;
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:50',
            'banking_list_id'=> 'required|string',
            'description' => 'nullable|string',
            'partner_code' => 'required|string|max:50',
            'thumb_path' => 'nullable|string',
            'owner' => 'required|string',
            'bank_number' => 'required|string',
            'branch' => 'nullable|string',
            'business' => 'required',
            'type' => 'in:topup,billing',
            'sort' => 'integer|nullable',
            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
        ];
    }
}
