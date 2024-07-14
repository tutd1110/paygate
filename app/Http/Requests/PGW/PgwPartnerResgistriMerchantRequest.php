<?php

namespace App\Http\Requests\PGW;

use App\Http\Requests\ValidateJsonResponse;
use App\Models\PGW\PgwPartner;
use Illuminate\Foundation\Http\FormRequest;

class PgwPartnerResgistriMerchantRequest extends FormRequest
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
        if ($this->method() == 'POST') {
            if ($this->input('payment_merchant_id', [])) {
                $countPaymentMerchant = sizeof($this->input('payment_merchant_id', []));
            } else {
                $countPaymentMerchant = 0;
            }
            return [
                'partner_code' => 'required|string|max:25|exists:' . (new PgwPartner())->getTable() . ',code',
                'payment_merchant_id' => 'array|required',
                'sort' => 'nullable|integer',
                'business' => 'array|required|size:' . $countPaymentMerchant,
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',

                'payment_merchant_id.*' => 'integer|required',
                'business.*' => 'string|required',
            ];
        } elseif($this->method() == 'PUT') {
            return [
                'partner_code' => 'required|string|max:25|exists:' . (new PgwPartner())->getTable() . ',code',
                'payment_merchant_id' => 'required|integer',
                'sort' => 'nullable|integer',
                'business' => 'required|string',
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                ];
        }
    }
}
