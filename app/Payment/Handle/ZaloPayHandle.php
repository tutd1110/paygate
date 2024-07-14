<?php

namespace App\Payment\Handle;

use App\Exceptions\PaymentException;
use Throwable;

class ZaloPayHandle extends PaymentException
{
    protected $response = [
        "return_code" => "",
        "return_message" => "",
    ];

    public function __construct(string $code, string $message = '', Throwable $previous = null, int $httpCode = 0, array $headers = [])
    {
        $this->response["return_code"] = $code;
        $this->response["return_message"] = $message;
        parent::__construct($message, $previous, $httpCode, $headers);
    }
}
