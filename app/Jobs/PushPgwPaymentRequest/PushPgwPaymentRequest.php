<?php

namespace App\Jobs\PushPgwPaymentRequest;

use App\Models\PGW\PgwBanks;
use App\Models\PGW\PgwPartnerRegistriBanking;
use App\Models\PGW\PgwPaymentMerchants;
use App\Models\PGW\PgwPaymentRequest;

use App\Models\PGW\PgwPaymentRequestMerchant;
use App\Repositories\PGW\PushPaymentRequestRepository;
use App\Repositories\PGW\PushPgwPaymentRequetsInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushPgwPaymentRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    /***
     * PushPgwOrder constructor.
     *
     * @param PgwPaymentRequest $pgwPaymentRequest
     */
    public $pgwPaymentRequest;
    public $status;
    public $merchants;
    public $paymentRequestMerchant;
    public $bankRegister;
    public $bank;

    public function __construct(PgwPaymentRequest $pgwPaymentRequest, $status = false, PgwPaymentMerchants $merchants, PgwPaymentRequestMerchant $paymentRequestMerchant, $bankRegister = [], $bank = [])
    {
        $this->pgwPaymentRequest = $pgwPaymentRequest;
        $this->status = $status;
        $this->merchants = $merchants;
        $this->paymentRequestMerchant = $paymentRequestMerchant;
        $this->bankRegister = $bankRegister;
        $this->bank= $bank;
        /***
         * gửi thông tin payment request sang url_return_api
         *
         */

    }

    public function backoff()
    {
        return [10, 20, 60, 3600, 86400];
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /***
         * @var $PushPaymentRequestRepository PushPaymentRequestRepository
         */
        $this->pushPaymentRequestRes = app()->make(PushPaymentRequestRepository::class);
        $this->pushPaymentRequestRes->pushPaymentRequest($this->pgwPaymentRequest,$this->status,$this->merchants, $this->paymentRequestMerchant,$this->bankRegister,$this->bank);
    }
}
