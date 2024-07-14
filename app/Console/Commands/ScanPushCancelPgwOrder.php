<?php

namespace App\Console\Commands;

use App\Jobs\PushOrderRequest\PushOrderRequest;
use App\Models\ContactExam;
use App\Models\ContactExamLog;
use App\Models\LandingPage;
use App\Models\PGW\PgwOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScanPushCancelPgwOrder extends Command
{
    const ORDER_STATUS_EXPIRED= 'expired';
    const PAYMENT_STATUS_UNSUCCESS = 'unsuccess';
    const ARRAY_STATUS_UPDATE_CANCEL = ['new', 'processing', 'waiting'];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan_push_cancel_pgw_orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PgwOrder $pgwOrders, LandingPage $landingPages)
    {
        $this->order = $pgwOrders;
        $this->landingPage = $landingPages;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . '] Start Run: ' . self::class . '');
        $this->scanPushCancelOrder();
        $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . '] Run Success: ' . self::class);
        return true;
    }


    public function scanPushCancelOrder()
    {
        $listLdp = $this->landingPage::query()
            ->where('auto_cancel_order', '!=', 0)
            ->get();
        $listLdpAutoCancelOrder = [];
        foreach ($listLdp as $key => $value) {
            $listLdpAutoCancelOrder[] = [
                'id' => $value['id'],
                'date' => Carbon::now()->subDay($value['auto_cancel_order'])->format('Y-m-d H:i:s')
            ];
        }
        $query = $this->order::query();
        $query->where(function ($query) use ($listLdpAutoCancelOrder) {
            foreach ($listLdpAutoCancelOrder as $k => $value) {
                if ($k == 0) {
                    $query = $query->where('landing_page_id', $value['id']);
                } else {
                    $query = $query->orWhere('landing_page_id', $value['id']);
                }
                $query = $query->where('created_at', '<=', $value['date']);
            }
        });
        $query = $query->whereIn('status', self::ARRAY_STATUS_UPDATE_CANCEL);
        $listOrder = $query->get();
        foreach ($listOrder as $key => $value) {
            $paymentRequest['total_pay'] = $value['amount'];
            $paymentRequest['paid_status'] = self::ORDER_STATUS_EXPIRED;
            $paymentRequest['merchant_code'] = $value['merchant_code'] ?? '';
            $pushOrder = PushOrderRequest::dispatch($value, $paymentRequest);
        }
        $query->update(['status' => self::ORDER_STATUS_EXPIRED]);
        echo ' Đã cập nhật : '.count($listOrder).' bản ghi!'.PHP_EOL;
    }
}

