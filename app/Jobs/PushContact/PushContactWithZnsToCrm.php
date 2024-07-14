<?php

namespace App\Jobs\PushContact;

use App\Models\ContactLeadProcess;
use App\Repositories\Contact\ContactPushEloquentRepository;
use App\Repositories\Contact\ContactPushRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushContactWithZnsToCrm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $contactLeadProcess;

    private $extraData;

    public $tries = 5;
    /***
     * PushReserveContact constructor.
     *
     * @param ContactLeadProcess $contactLeadProcess
     */
    public function __construct(ContactLeadProcess $contactLeadProcess, array $extraData= [])
    {
        $this->contactLeadProcess = $contactLeadProcess;
        $this->extraData = $extraData;

        /***
         * gửi thông tin contact lên server
         *
         */

    }

    public function backoff()
    {
        return [10, 20, 60, 3600, 86400];
    }
    public function handle()
    {
        /***
         * @var $contactPushRes ContactPushEloquentRepository
         */
        $contactPushRes = app()->make(ContactPushRepositoryInterface::class);

        $contactPushRes->pushContactLeadProcess($this->contactLeadProcess, $this->extraData);
    }
}
