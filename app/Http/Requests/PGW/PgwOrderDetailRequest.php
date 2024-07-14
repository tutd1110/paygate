<?php

namespace App\Http\Requests\PGW;

use App\Http\Requests\ValidateJsonResponse;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwOrderDetail;
use Illuminate\Foundation\Http\FormRequest;

class PgwOrderDetailRequest extends FormRequest
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
        if ($this->multiple == true) {
            $countProduct = sizeof($this->input('id', []));
            return [
                'id'=> 'array|required',
                'description' => 'array|size:'.$countProduct,
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',

                'id.*' => 'required|integer',
                'description.*' => 'string|nullable',
            ];
        } else {
            $orderDetail = PgwOrderDetail::find($this->order_detail_id);
            return [
                'order_id' => 'nullable|integer|exists:' . (new PgwOrder())->getTable() . ',id',
                'product_id' => 'nullable|string',
                'product_type' => 'in:package,combo',
                'product_name' => 'nullable|string',
                'quantity' => 'nullable|integer',
                'price' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'is_refund' => 'nullable|in:yes,no|not_in:' . $orderDetail->is_refund,
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
            ];
        }
    }
}
