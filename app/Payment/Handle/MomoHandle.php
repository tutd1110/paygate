<?php

namespace App\Payment\Handle;

use App\Exceptions\PaymentException;
use Throwable;

class MomoHandle extends PaymentException
{
    protected $response = [
        "RspCode"=> "",
        "message" => "",
    ];

    public function __construct(string $message = '',string $code= '', Throwable $previous = null, int $httpCode = 0, array $headers = [])
    {
        $this->response["RspCode"] = $code;
        $this->response["message"] = $message;
        parent::__construct($message, $previous, $httpCode, $headers);
    }
}
