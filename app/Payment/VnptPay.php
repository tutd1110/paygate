<?php

namespace App\Payment;

use App\Helper\Request;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPaymentRequestMerchant;

class VnptPay extends PayGate
{
    protected $handle = "App\Payment\Handle\VnptPayHandle";

    /**
     * Return pay url
     */
    function getPayUrl(): string
    {
        // TODO
        return "";
    }

    /**
     * Verify bill
     * @throws VnptPayHandle
     */
    public function getBill(array $params)
    {
        // TODO
    }

    function getMerchantInvoice(): string
    {
        // TODO
        return "";
    }

    /**
     * @throws VnptPayHandle
     */
    function payBill(array $params)
    {
        // TODO
    }

    private function valid($validData)
    {
        // TODO
    }

    function validHashData($validData, $data): array
    {
        // TODO
        return [];
    }

    function createChecksum($params): string
    {
        // TODO
        return "";
    }

    function payGateResponseCode()
    {
        // TODO
        return "";
    }

    public function doRequest($endpoint, $data, $method = 'POST')
    {
        // TODO
    }
}
