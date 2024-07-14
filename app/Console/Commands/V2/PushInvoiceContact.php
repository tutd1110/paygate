<?php

namespace App\Console\Commands\V2;

use App\Lib\PushContactStatus;
use App\Models\Invoice\Invoice;
use App\Repositories\Contact\ContactPushEloquentRepository;
use App\Repositories\Contact\ContactPushRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PushInvoiceContact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push_invoice_contact';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /***
     * @var ContactPushRepositoryInterface|ContactPushEloquentRepository
     */
    private $contactPushRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->contactPushRepository = app()->make(ContactPushRepositoryInterface::class);
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
        $this->pushContactIfNotPaid();
        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Run Success: '.self::class);

        return 0;
    }

    public function pushContactIfNotPaid()
    {
        /***
         * @change log
         * đổi từ 12 tiếng xuống còn 1 giờ
         */
        $listInvoices = Invoice::query()
            ->where('is_crm_pushed', 0)
            ->whereIn('status', [
                'new',
                'processing',
                'cancel'
            ])
            ->where('is_must_push_contact_unpaid', 1)
            ->where('is_crm_pushed', 0)
            ->whereNull('pushed_contact_unpaid_at')
            ->where('must_push_contact_unpaid_after_time', '<=', Carbon::now())->get();

        foreach ($listInvoices as $invoice) {
            try {
                if ($invoice->contact) {
                    $contact = $invoice->contact;
                    $contact->description = $contact->description.' Chưa thanh toán đơn hàng '.$invoice->code
                        .' trạng thái: '
                        .$invoice->status;
                    $contact->save();
                    $this->contactPushRepository->pushContactLeadProcess($contact, [
                        'status' => PushContactStatus::CREATE_BILL,
                        'line' => $invoice->line ?? '',
                    ]);
                    $invoice->is_crm_pushed = 1;
                    $invoice->pushed_contact_unpaid_at = Carbon::now();
                    $invoice->save();
                }
            } catch (\Exception $exception) {
                Log::error($exception);
            }

        }
    }
}
