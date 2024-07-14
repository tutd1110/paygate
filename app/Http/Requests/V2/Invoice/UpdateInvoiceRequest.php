<?php

namespace App\Http\Requests\V2\Invoice;

use App\Http\Requests\ProcessNullValidate;
use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    use ValidateJsonResponse;
    use ProcessNullValidate;

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
        return $this->processBeforeUpdate([
            'status' => 'string|in:new,processing,paid,cancel',
            'active_code' => 'nullable|string',
            'merchant_code' => 'nullable|string|max:35',
            'is_active_code_used' => 'nullable|in:1,0'
        ]);
    }
}
