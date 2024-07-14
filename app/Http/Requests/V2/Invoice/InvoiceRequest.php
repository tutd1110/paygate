<?php

namespace App\Http\Requests\V2\Invoice;

use App\Http\Requests\ProcessNullValidate;
use App\Http\Requests\ValidateJsonResponse;
use App\Models\LandingPage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

class InvoiceRequest extends FormRequest
{

    use ProcessNullValidate;

    function validationData()
    {
        $data = parent::validationData();

        if (isset($data['classid'])) {
            /***
             * format class id to class
             */
            try {
                $data['class'] = trim(explode('|', $data['classid'])[0]);
            } catch (\Exception $exception) {

            }

        }

        return $data;
    }

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

        $countProduct = sizeof($this->input('item_product_id', []));
        $validate = [

            'user_id' => 'nullable|integer',
            'merchant_code' => 'nullable|string|max:35',
            'landing_page_id' => 'required|integer|exists:'.(new LandingPage())->getTable().',id',
            'full_name' => 'required|string',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'email' => 'nullable|string',
            'class' => 'nullable|integer',
            'action' => 'string',
            'crm_type' => 'string|nullable',
            'description' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
            'utm_source' => 'nullable|string',
            'utm_medium' => 'nullable|string',
            'ip_register' => 'string',
            'return_url_true' => 'string|nullable',
            'return_url_false' => 'string|nullable',
            'return_data' => 'string|nullable',

            'is_api' => 'nullable|integer|in:0,1',
            'redirect_if_error' => 'nullable|string',

            'line' => 'string|nullable',

            'hm_order_id' => 'integer|nullable',
            'amount' => 'numeric|nullable',
            'discount' => 'numeric|nullable',
            'voucher_code' => 'nullable|string',

            'item_product_id' => 'array|required',
            'item_product_name' => 'array|nullable',
            'item_product_type' => 'array|required',
            'item_quantity' => 'array|nullable',
            'item_price' => 'array|required|size:'.$countProduct,
            'item_discount' => 'array|nullable',

            'item_product_id.*' => 'string|required',
            'item_product_type.*' => 'required|string|in:combo,package',
            'item_product_name.*' => 'required|string|nullable',
            'item_quantity.*' => 'nullable|integer',
            'item_price.*' => 'required|numeric',
            'item_discount.*' => 'nullable|numeric',
        ];

        return $validate;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->myValidate();
    }

    public function failedValidation(Validator $validator)
    {
        $data = $this->validationData();
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

    protected function getRedirectUrl()
    {
        $data = $this->validationData();
        if (($data['redirect_if_error'] ?? false)) {
            return $data['redirect_if_error'];
        }
    }


}
