<?php

namespace App\Http\Requests\Gifts;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class RunWheelFahasaRequest extends FormRequest
{
    use ValidateJsonResponse;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
            'landing_page_id' =>'required|integer',
            'contact_id'=> 'required|integer',
            'bill_code' => 'required|string',
            'test' => 'integer',
        ];
    }
}
