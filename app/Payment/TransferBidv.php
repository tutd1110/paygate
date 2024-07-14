<?php

namespace App\Payment;

use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\PGW\PgwPaymentRequestMerchant;
use App\Payment\Handle\TransferBIDVHandle;

class TransferBidv extends PayGate
{
    protected $handle = "App\Payment\Handle\TransferBIDVHandle";

    /**
     * Return pay url
     */
    function getPayUrl(): string
    {
        try {
            $this->createRequest();
            $this->createRequestMerchant();
            $result = "";
            if ($this->bankRegister) {
                $result .= $this->bankRegister->code;
            }
            $result .= $this->getTransactionCode();
            return $result;
        } catch (\Exception $e) {
            return "";
        }
    }

    /**
     * Verify bill
     * @throws TransferBIDVHandle
     */
    public function getBill()
    {
        try {
            $this->createPayLog("check");
            $validData = [
                "service_id",
                "customer_id",
            ];
            $this->valid($validData);
        } catch (TransferBIDVHandle $e) {
            $this->updatePayLog($e->getResponse());
            throw $e;
        } catch (\Exception $e) {
            $handle = new $this->handle($e->getMessage());
            $this->updatePayLog($handle->getResponse());
            throw $handle;
        }

        //$transactionCode = $this->order->code;

        $data = [
            "service_id" => $this->payGateData['service_id'],
            "customer_info" => [
                "customer_id" => $this->getTransactionCode(),
                "customer_name" => $this->customer->full_name,
                "customer_addr" => ($this->customer->address) ? $this->customer->address : "",
                "bill_id" => $this->getTransactionCode(),
                "bill_period" => date('m/Y', strtotime($this->requestMerchant->created_at)),
                "bill_amount" => strval($this->getAmount()),
                "bill_info" => "Hocmai TT hoc phi " . $this->getTransactionCode(),
            ],
        ];

        $response = [
            "result_code" => "000",
            "result_desc" => "success",
            "data" => $data
        ];
        $this->updatePayLog($response);
        return response()->json($response);
    }

    /**
     * @throws TransferBIDVHandle
     */
    function payBill()
    {
        try {
            $this->createPayLog("confirm");
            $validData = [
                "trans_id",
                "bill_id",
                "amount",
            ];
            $this->valid($validData);

            if ($this->getAmount() != intval($this->payGateData['amount'])) {
                throw new $this->handle("026", "Số tiền thanh toán không khớp giá trị đơn hàng");
            }
        } catch (TransferBIDVHandle $e) {
            $this->updatePayLog($e->getResponse());
            throw $e;
        } catch (\Exception $e) {
            $handle = new $this->handle($e->getMessage());
            $this->updatePayLog($handle->getResponse());
            throw $handle;
        }

        $response = [
            "result_code" => "000",
            "result_desc" => "success",
            "RspCode" => "00"
        ];
        $this->updatePayLog($response, true);

        $this->paymentSuccess(true);

        return response()->json($response);
    }

    private function valid($validData)
    {
        $data = $this->payGateData;
        if (!isset($data['checksum'])) {
            throw new $this->handle("145", 'Chữ ký không xác định');
        }

        $hashData = $this->validHashData($validData, $data);
        if (count($validData) != count($hashData)) {
            throw new $this->handle("145", "Thiếu dữ liệu đầu vào");
        }

        $secretKey = (isset($this->config->secretKey)) ? $this->config->secretKey : config('payment.transfer.secretKey');
        array_unshift($hashData, $secretKey);

        if ($this->createChecksum($hashData) != $data['checksum']) {
            throw new $this->handle("007", "Sai chữ ký");
        }

       // $vpc_MerchTxnRef = $this->bankRegister->code.$data['customer_id'];
        $this->initOrder($data['customer_id']);
        if (!$this->order || !$this->request || !$this->requestMerchant) {
            throw new $this->handle("025", "Khách hàng không có đơn hàng");
        }

        if (!$this->customer) {
            throw new $this->handle("017", "Khách hàng không tồn tại");
        }

        if ($this->requestMerchant->paid_status == PgwPaymentRequestMerchant::PAID_STATUS_SUCCESS
            || $this->request->paid_status == PgwPaymentRequest::PAID_STATUS_SUCCESS
            || $this->order->status == PgwOrder::STATUS_PAID
            || $this->order->status == PgwOrder::STATUS_FAIL
            || $this->order->status == PgwOrder::STATUS_REFUND
            || $this->order->status == PgwOrder::STATUS_CANCEL
            || $this->order->status == PgwOrder::STATUS_EXPIRED
        ) {
            throw new $this->handle("001", "Hóa đơn đã gạch nợ");
        }
    }

    function createChecksum($params): string
    {
        return md5(implode('|', $params));
    }

    function payGateVpcMerchTxnRef(): string
    {
        if (isset($this->payGateData['customer_id'])) {
            return $this->payGateData['customer_id'];
        }

        return "";
    }

    function getMerchantInvoice(): string
    {
        if (isset($this->payGateData['trans_id'])) {
            return $this->payGateData['trans_id'];
        }

        return "";
    }

    function payGateResponseCode()
    {
        return "0";
    }

    public function getQRUrl(): string
    {
        $number = $this->getPayUrl();
        $amount = $this->getAmount();
        $content = $this->order->code;
        if ($this->customer) {
            $content .= " " . $this->customer->phone;
        }
        return "https://img.vietqr.io/image/970418-$number-".config('payment.template_img_qr.bidv').".jpg?amount=$amount&addInfo=$content";
    }
}
