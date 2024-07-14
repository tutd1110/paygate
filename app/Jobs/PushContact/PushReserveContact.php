<?php

namespace App\Jobs\PushContact;

use App\Models\ContactLead;
use App\Repositories\Contact\ContactPushEloquentRepository;
use App\Repositories\Contact\ContactPushRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushReserveContact implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $contact;

    private $extraData;

    public $tries = 5;
    /***
     * PushReserveContact constructor.
     *
     * @param ContactLead $contactLead
     */
    public function __construct(ContactLead $contactLead, array $extraData)
    {
        $this->contact = $contactLead;
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
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /***
         * @var $contactPushRes ContactPushEloquentRepository
         */
        $contactPushRes = app()->make(ContactPushRepositoryInterface::class);

        $contactPushRes->pushReserveContact($this->contact, $this->extraData);
    }
}
