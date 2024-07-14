<?php

namespace App\Payment\Handle;

use App\Exceptions\PaymentException;
use Throwable;

class TransferBIDVHandle extends PaymentException
{
    protected $response = [
        "result_code" => "",
        "result_desc" => "",
        "data" => [],
    ];

    public function __construct(string $code, string $message = '', ?array $data = [], Throwable $previous = null, int $httpCode = 0, array $headers = [])
    {
        $this->response["result_code"] = $code;
        $this->response["result_desc"] = $message;
        $this->response["data"] = $data;
        parent::__construct($message, $previous, $httpCode, $headers);
    }
}
