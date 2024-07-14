<?php

namespace App\Console\Commands\V2;

use App\Models\Invoice\Invoice;
use App\Repositories\Invoice\InvoiceInterface;
use App\Repositories\Invoice\InvoiceRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSmsUnpaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:unpaid';

    /***
     * @var $InvoiceRepository InvoiceRepository | InvoiceInterface
     */
    private $InvoiceRepository;

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
    public function __construct()
    {
        $this->InvoiceRepository = app()->make(InvoiceInterface::class);
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Start Run: '.self::class.'');
        /***
         * Lấy danh sách invoice chưa thanh toán hơn 1 giờ
         */
        $listInvoiceUnpaid = Invoice::whereIn('status', [
            'new',
            'processing'
        ])
            ->where('is_must_send_sms_unpaid', 1)
            ->where('must_send_sms_unpaid_after_time', '<=', Carbon::now())
            ->whereNull('sent_sms_unpaid_at')
            ->where('is_send_sms_unpaid', 0)
            ->get();

        foreach ($listInvoiceUnpaid as $invoice) {
            try {
                $this->InvoiceRepository->sendSmsUnpaid($invoice);
            } catch (\Exception $exception) {
                /***
                 * skip error and continue
                 */
                Log::error($exception);
            }

        }
        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Run Success: '.self::class);

        return 0;
    }
}
