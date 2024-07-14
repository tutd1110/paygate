<?php

namespace App\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

trait ValidateJsonResponse
{
    public function failedValidation(Validator $validator)
    {
        try {
            /***
             * Thêm log exception khi validate không thành công
             */
            $exception = new ValidationException($validator);
            Log::warning($exception->getMessage(), [
                'error_data' => $validator->errors(),
                'error_url' => $this->fullUrl(),
                'data' => $this->all(),
                'e' => $exception
            ]);
        } catch (\Exception $exception) {
           Log::error($exception);
        }

        $exception = new HttpResponseException(
            response()->json([
                'message' => $validator->errors()->first(),
                'data' => $validator->errors()
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );


        throw $exception;
    }
}
