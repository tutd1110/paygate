<?php

namespace App\Http\Requests\PGW;

use App\Http\Requests\ValidateJsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

class PgwStatisticalRequest extends FormRequest
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
            $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'error=' . $validator->errors()->first();

            throw (new ValidationException($validator))
                ->redirectTo($url);
        }
    }
    public function rules()
    {
        $rule = [
            'year' => 'integer|nullable',
            'selectMonth' => 'in:true,false|nullable',
            'month' => 'integer|nullable|required_if:selectMonth,==,true',
        ];
        return $rule;
    }
}
