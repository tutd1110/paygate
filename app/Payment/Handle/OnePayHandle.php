<?php

namespace App\Payment\Handle;

use App\Exceptions\PaymentException;
use Throwable;

class OnePayHandle extends PaymentException
{
    protected $response = [
        "vpc_TxnResponseCode" => "",
        "vpc_Message" => "",
    ];

    public function __construct(string $code = '', string $message = '', Throwable $previous = null, int $httpCode = 0, array $headers = [])
    {
        $this->response["vpc_TxnResponseCode"] = $code;
        $this->response["vpc_Message"] = $message;
        parent::__construct($message, $previous, $httpCode, $headers);
    }
}
