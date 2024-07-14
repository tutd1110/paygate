<?php

namespace App\Http\Requests\PGW;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class PgwPaymentMerchantRequest extends FormRequest
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
        return [
            'code'=>'required|max:25',
            'name'=>'required',
            'thumb_path'=>'nullable|string',
            'type' => 'in:paygate,transfer',
            'pay_gate' => 'nullable|string|max:255',
            'status'=> 'in:active,inactive',
            'sort'=>'nullable|integer',
            'description'=>'string|nullable',
            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
        ];
    }
}
