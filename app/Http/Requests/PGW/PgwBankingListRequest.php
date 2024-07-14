<?php

namespace App\Http\Requests\PGW;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class PgwBankingListRequest extends FormRequest
{
    use ValidateJsonResponse;
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' =>'required|string|max:50',
            'name' =>'required|string',
            'thumb_path'=>'nullable|string',
            'status' => 'in:active,inactive',
            'created_by'=>'integer|nullable',
            'updated_by'=>'integer|nullable',
        ];
    }
}
