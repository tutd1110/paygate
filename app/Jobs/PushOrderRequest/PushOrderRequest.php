<?php

namespace App\Jobs\PushOrderRequest;

use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPaymentMerchants;
use App\Models\PGW\PgwPaymentRequest;

use App\Repositories\PGW\PgwOrderRepository;
use App\Repositories\PGW\PushPaymentRequestRepository;
use App\Repositories\PGW\PushPgwPaymentRequetsInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushOrderRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    /***
     * PushPgwOrder constructor.
     *
     * @param PgwOrder $pgwOrder
     */
    public $pgwOrder;
    public $pgwPaymentRequest;

    public function __construct(PgwOrder $pgwOrder, $pgwPaymentRequest)
    {
        $this->pgwOrder = $pgwOrder;
        $this->pgwPaymentRequest = $pgwPaymentRequest;
        /***
         * gửi thông tin payment request sang url_return_api
         *
         */

    }

    public function backoff()
    {
        return [10, 20, 60, 1800, 3600];
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /***
         * @var $pgwOrderRepository PgwOrderRepository
         */
        $this->pgwOrderRepository = app()->make(PgwOrderRepository::class);
        $this->pgwOrderRepository->pushOrderRequest($this->pgwOrder,$this->pgwPaymentRequest);
    }
}
