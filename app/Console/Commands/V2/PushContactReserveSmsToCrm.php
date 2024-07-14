<?php

namespace App\Console\Commands\V2;

use App\Models\ContactLeadProcessReserveLog;
use App\Repositories\Contact\ContactPushEloquentRepository;
use App\Repositories\Contact\ContactPushRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PushContactReserveSmsToCrm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push_sms_reserve_contact';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /***
     * @var ContactPushRepositoryInterface | ContactPushEloquentRepository
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

        $this->pushNotSendAfterHour();
        $this->pushSent();

        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Run Success: '.self::class);


        return 0;
    }

    public function pushNotSendAfterHour()
    {
        /****
         * kiểm tra các contact chưa nhắn tin nhưng đã hơn 1h để push vào crm xong đánh dấu là đã push crm
         */


        $listReserveNotSent = ContactLeadProcessReserveLog::with([
            'contactLeadProcess',
        ])
            ->where('status', 'create')
            ->where('created_at', '<=', Carbon::now()->subHour())
            ->where('is_has_from_reserve_form', 1)
            ->where('is_crm_pushed', 0)->get();

        foreach ($listReserveNotSent as $eachLog) {
            try {
                $contactLeadProcess = $eachLog->contactLeadProcess;
                $this->contactPushRepository->pushContactLeadProcess($contactLeadProcess);
                $eachLog->is_crm_pushed = 1;
                $eachLog->crm_pushed_at = Carbon::now();
                $eachLog->save();
            } catch (\Exception $exception) {
                /***
                 * Trường hợp lỗi tạm bỏ qua`
                 */
//                throw $exception;
                Log::error($exception);
            }

        }
    }

    public function pushSent()
    {
        /***
         * Trường hợp đã gửi tin nhắn mà chưa push thì push
         */
        $listReserveSent = ContactLeadProcessReserveLog::query()
            ->where('status', 'sent_sms_reserve')
            ->where('is_crm_pushed', 0)
            ->where('is_has_from_reserve_form', 1)
            ->get();

        foreach ($listReserveSent as $eachLog) {
            try {
                $contactLeadProcess = $eachLog->contactLeadProcess;
                $this->contactPushRepository->pushContactLeadProcess($contactLeadProcess, [
                    'line' => $eachLog->line,
                ]);
                $eachLog->is_crm_pushed = 1;
                $eachLog->crm_pushed_at = Carbon::now();
                $eachLog->save();
            } catch (\Exception $exception) {
                /***
                 * Trường hợp lỗi tạm bỏ qua
                 */
                Log::error($exception);
            }
        }
    }
}
