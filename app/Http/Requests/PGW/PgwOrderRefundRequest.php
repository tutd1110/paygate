<?php

namespace App\Http\Requests\PGW;

use App\Http\Requests\ValidateJsonResponse;
use App\Models\LandingPage;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPartner;
use Illuminate\Foundation\Http\FormRequest;

class PgwOrderRefundRequest extends FormRequest
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
       if ($this->method() == 'POST'){
        return [
            'order_id' => 'required|integer|exists:' . (new PgwOrder())->getTable() . ',id',
            'landing_page_id' => 'required|integer|exists:' . (new LandingPage())->getTable() . ',id',
            'partner_code' => 'required|string|max:25|exists:' . (new PgwPartner())->getTable() . ',code',
            'refund_value' => 'required|integer',
            'description' => 'nullable|string',
            'status' => 'in:request,refused,appoved,finish',
            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
        ];
       }elseif ($this->method() == 'PUT'){
           return [
               'status' => 'in:request,refused,appoved,finish',
               'updated_by' => 'nullable|integer',
           ];
       }
    }
}
