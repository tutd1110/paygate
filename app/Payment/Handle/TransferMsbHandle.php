<?php

namespace App\Payment\Handle;

use App\Exceptions\PaymentException;
use Throwable;

class TransferMsbHandle extends PaymentException
{
    protected $response = [
        "respMessage" => "",
        "respDomain" => "",
    ];

    public function __construct(string $code, string $message = '', ?array $data = [], Throwable $previous = null, int $httpCode = 0, array $headers = [])
    {
        $this->response["respMessage"] = [
            "respCode" => $code,
            "respDesc" => $message
        ];
        $this->response["respDomain"] = $data;
        parent::__construct($message, $previous, $httpCode, $headers);
    }
}
