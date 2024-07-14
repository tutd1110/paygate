<?php

namespace App\Payment\Handle;

use App\Exceptions\PaymentException;
use Throwable;

class VnpayHandle extends PaymentException
{
    protected $response = [
        "RspCode" => "",
        "Message" => "",
    ];

    public function __construct(string $code = '', string $message = '', Throwable $previous = null, int $httpCode = 0, array $headers = [])
    {
        $this->response["RspCode"] = $code;
        $this->response["Message"] = $message;
        parent::__construct($message, $previous, $httpCode, $headers);
    }
}
