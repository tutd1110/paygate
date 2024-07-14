<?php

namespace App\Http\Requests\PGW;

use App\Http\Requests\ValidateJsonResponse;
use App\Models\LandingPage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Models\PGW\PgwPartner;

class PgwOrderRequest extends FormRequest
{
    use ValidateJsonResponse;
    public function authorize()
    {
        return true;
    }


    public function failedValidation(Validator $validator)
    {
        $data = parent::validationData();
        if (!($data['redirect_if_error'] ?? false)) {
            $exception = new HttpResponseException(
                response()->json([
                    'message' => $validator->errors()->first(),
                    'data' => $validator->errors()
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
            );

            throw $exception;
        } else {
            $url = $this->getRedirectUrl();
            $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?').'error='.$validator->errors()->first();

            throw (new ValidationException($validator))
                ->redirectTo($url);
        }
    }
    public function rules()
    {
        $countProduct = sizeof($this->input('item_product_id', []));
        $rule =  [
            'full_name'=>'required|string',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'email' => 'nullable|string',
            'redirect_if_error'=>'nullable|string',
            'utm_campaign' => 'nullable|string',
            'utm_source' => 'nullable|string',
            'utm_medium' => 'nullable|string',
            'utm_content'=> 'nullable|string',
            'utm_term'=>'nullable|string',
            'utm_creator'=>'nullable|string',
            'bill_code'=>'nullable|string|max:50',
            'code' => 'nullable|string|max:50',
            'partner_code' => 'required|string|max:50|exists:'.(new PgwPartner())->getTable().',code',
            'landing_page_id' => 'required|integer|exists:'.(new LandingPage())->getTable().',id',
            'contact_lead_process_id' => 'nullable|integer',
            'order_client_id' => 'nullable|integer',
            'coupon_code' => 'nullable|string|max:25',
            'is_api' => 'in:yes,no',
            'merchant_code' => 'nullable|string|max:10',
            'banking_code' => 'nullable|required_if:merchant_code,==,transfer|string|max:10',
            'return_url_true' => 'nullable|string',
            'return_url_false' => 'nullable|string',
            'return_data' => 'nullable|string',
            'url_return_api' => 'nullable|string',
            'custom'=>'nullable|string',

            'item_product_id' => 'array|required',
            'item_product_name' => 'array|required',
            'item_product_type' => 'array|nullable|size:'.$countProduct,
            'item_quantity' => 'array|nullable',
            'item_price' => 'array|required|size:'.$countProduct,
            'item_discount' => 'array|nullable',

            'item_product_id.*' => 'string|required',
            'item_product_type.*' => 'nullable|string|in:combo,package',
            'item_product_name.*' => 'required|string|nullable',
            'item_quantity.*' => 'nullable|integer',
            'item_price.*' => 'required|numeric',
            'item_discount.*' => 'nullable|numeric',
            'description' => 'nullable|string',
            'line' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'discount_detail' => 'nullable',
        ];

        if ($this->method() == 'PUT') {
            $rule['full_name'] = 'nullable|string';
            $rule['phone'] = 'nullable|string';
            $rule['landing_page_id'] = 'nullable|integer|exists:'.(new LandingPage())->getTable().',id';
            $rule['contact_lead_process_id'] = 'nullable|integer';
            $rule['item_product_id'] = 'nullable|array';
            $rule['item_price'] = 'nullable|array';
            $rule['item_product_id.*'] = 'nullable|string';
            $rule['item_product_name.*'] = 'nullable|string';
            $rule['item_price.*'] = 'nullable|numeric';
            $rule['partner_code'] = 'nullable|string|max:50';
            $rule['item_product_name'] = 'nullable|array';
            $rule['description'] = 'nullable|string';
            $rule['line'] = 'nullable|string';
        }
        return $rule;
    }

    public function messages()
    {
        $countProduct = sizeof($this->input('item_product_id', []));
        $messages = [
            'full_name.required' => 'Tên khách hàng không được để trống!',
            'full_name.string' => 'Tên khách hàng phải là dạng chuỗi!',
            'phone.required' => 'Số điện thoại không được để trống',
            'address.string' => 'Địa chỉ phải là dạng chuỗi',
            'email.email' => 'Email chưa đúng định dạng',
            'bill_code.max' => 'Mã Bill không quá 50 kí tự',
            'bill_code.string' => 'Mã Bill phải là dạng chuỗi',
            'code.max' => 'Mã Bill không quá 50 kí tự',
            'code.string' => 'Mã Bill phải là dạng chuỗi',
            'partner_code.required' => 'Mã đối tác không được để trống',
            'partner_code.string' => 'Mã đối tác phải là dạng chuỗi',
            'partner_code.max' => 'Mã đối tác không quá 50 kí tự',
            'partner_code.exists' => 'Mã đối tác đã tồn tại',
            'landing_page_id.required' => 'landing_page_id không được được trống',
            'landing_page_id.integer' => 'landing_page_id phải là dạng số',
            'landing_page_id.exists' => 'landing_page_id đã tồn tại',
            'contact_lead_process_id.integer' => 'Id khách hàng phải là dạng số',
            'order_client_id.integer' => 'order_client_id phải là dạng số ',
            'coupon_code.string' => 'Mã giảm giá phải là dạng chuỗi ',
            'coupon_code.max' => 'Mã giảm giá không quá 25 kí tự',
            'is_api.in' => 'is_api không hợp lệ',
            'merchant_code.string' => 'Mã cổng thanh toán phải là dạng chuỗi',
            'merchant_code.max' => 'Mã cổng thanh toán không quá 10 kí tự',
            'banking_code.required_if' => 'Mã ngân hàng không được trống',
            'banking_code.string' => 'Mã ngân hàng phai là dạng chuỗi',
            'banking_code.max' => 'Mã ngân hàng không quá 10 kí tự',
            'return_url_true.string' => 'return_url_true phải là dạng chuỗi',
            'return_url_false.string' => 'return_url_false phải là dạng chuỗi',
            'return_data.string' => 'return_data phải là dạng chuỗi',
            'url_return_api.string' => 'url_return_api phải là dạng chuỗi',
            'custom.string' => 'custom phải là dạng chuỗi',

            'item_product_id.array' => 'item_product_id phải là dạng mảng',
            'item_product_id.required' => 'item_product_id không được để trống',
            'item_product_name.array' => 'item_product_name phải là dạng mảng',
            'item_product_name.required' => 'item_product_name không được để trống',
            'item_product_type.array' => 'item_product_type phải là dạng mảng',
            'item_product_type.size' => 'Mảng item_product_type phải chứa '.$countProduct.' phần tử',
            'item_quantity.array' => 'item_quantity phải là dạng mảng',
            'item_price.required' => 'item_price không được trống',
            'item_price.array' => 'item_price phải là dạng mảng',
            'item_price.size' => 'Mảng item_price phải chứa '.$countProduct.' phần tử',
            'item_discount.array' => 'item_discount phải là dạng mảng',

            'item_product_id.*.string' => 'Id sản phẩm không được có kí tự đặc biệt',
            'item_product_id.*.required' => 'Id sản phẩm không được trống',
            'item_product_type.*.string' => 'Loại sản phẩm phải là dạng chuỗi',
            'item_product_type.*.in' => 'Loại sản phẩm không đúng định dạng',
            'item_product_name.*.required' => 'Tên sản phẩm không được để trống',
            'item_product_name.*.string' => 'Tên sản phẩm phải là dạng chuỗi',
            'item_quantity.*.integer' => 'Số lượng sản phẩm là dạng số',
            'item_price.*.required' => 'Giá sản phẩm không được trống',
            'item_price.*.numeric' => 'Giá sản phẩm phải là dạng số',
            'item_discount.*.numeric' => 'Số giảm giá phải là dạng số',
            'description.string' => 'Mô tả phải là dạng chuỗi',
            'line.string' => 'Mô tả phải là dạng chuỗi',
            'discount.numeric' => 'Số giảm giá thì phải là dạng số',
        ];
        return $messages;
    }
}
