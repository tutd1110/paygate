<?php

namespace App\Http\Requests\PGW;

use App\Http\Requests\ValidateJsonResponse;
use App\Models\PGW\PgwPartner;
use Illuminate\Foundation\Http\FormRequest;

class PgwPaymentRequestRequest extends FormRequest
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
            'partner_code' => 'required|string|max:25|exists:'.(new PgwPartner())->getTable().',code',
            'order_client_id' => 'required|integer',
            'merchant_id' => 'nullable|integer',
            'banking_id' => 'nullable|integer',
            'payment_code' => 'nullable|string|max:25',
            'transsion_id' => 'nullable|integer',
            'payment_value' => 'required|integer',
            'total_pay'=> 'nullable|integer',
            'paid_status'=>'in:success,unsuccess',
            'url_return_true'=>'nullable|string',
            'url_return_false' => 'nullable|string',
            'url_return_api' => 'nullable|string',
            'custom'=> 'in:active,inactive',
            'is_msb_va'=>'in:yes,no',
            'created_at' => 'nullable',
            'updated_at' => 'nullable'
        ];
    }
}
