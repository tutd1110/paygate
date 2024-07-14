<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\ProcessNullValidate;
use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class InvoiceRequest extends FormRequest
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
    public function myValidate()
    {
        $validate = [
            'landing_page_id' => 'integer|required',
            'hm_order_id' => 'integer|nullable',
            'user_id' => 'integer|required',
            'amount' => 'numeric|required',
            'discount' => 'numeric|nullable',
            'voucher_code' => 'nullable|string',
            'quantity' => 'integer|required',
            'status' => 'string|in:new,processing,paid,cancel',
            'created_by' => 'integer|nullable',
            'updated_by' => 'integer|nullable',

            'item_product_id' => 'array|required',
            'item_product_name' => 'array|nullable',
            'item_product_type' => 'array|required',
            'item_quantity' => 'array|required',
            'item_price' => 'array|required',
            'item_discount' => 'array|required',


            'item_product_id.*' => 'string|required',
            'item_product_type.*' => 'required|string|in:combo,package',
            'item_product_name.*' => 'required|string|nullable',
            'item_quantity.*' => 'required|integer',
            'item_price.*' => 'required|numeric',
            'item_discount.*' => 'nullable|numeric',
        ];

        if (Str::upper($this->method()) == 'PUT' || Str::upper($this->method()) == 'PATCH') {
            /***
             * nếu update bắt buộc phải có code
             */
            $validate = array_merge($validate, [
                'code' => 'required|string',
            ]);

            if ($this->input('item_product_id')) {
                $numberItem = sizeof($this->input('item_product_id', null));

                $validate = array_merge($validate, [
                    'item_product_id' => 'array|size:'.$numberItem,
                    'item_product_type' => 'array|size:'.$numberItem,
                    'item_quantity' => 'array|size:'.$numberItem,
                    'item_price' => 'array|size:'.$numberItem,
                    'item_discount' => 'array|size:'.$numberItem,
                ]);
            }
        }

        return $validate;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->processBeforeUpdate($this->myValidate());
    }


}
