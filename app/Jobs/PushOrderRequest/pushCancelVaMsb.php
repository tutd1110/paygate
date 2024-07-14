<?php

namespace App\Jobs\PushOrderRequest;

use App\Models\PGW\PgwOrder;
use App\Payment\TransferMsb;
use App\Repositories\PGW\PgwOrderRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class pushCancelVaMsb implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    /***
     * PushPgwOrder constructor.
     *
     * @param PgwOrder $pgwOrder
     */
    public $pgwOrder;

    public function __construct(PgwOrder $pgwOrder)
    {
        $this->pgwOrder = $pgwOrder;
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
         * @var $pgwOrderRepository PgwOrderRepository
         */
        $this->transferMSB = app()->make(TransferMsb::class);
        $this->transferMSB->cancelVirtualAccount($this->pgwOrder);
    }
}
