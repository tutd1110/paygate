<?php

namespace App\Jobs\PushContact;

use App\Models\ContactLead;
use App\Repositories\Contact\ContactPushEloquentRepository;
use App\Repositories\Contact\ContactPushRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushContactToCrm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $httpClient;
    public $tries = 5;
    private $contact;

    public function backoff()
    {
        return [10, 20, 60, 3600, 86400];
    }

    /***
     * PushContactToCrm constructor.
     *
     * @param ContactLead $contact
     */
    public function __construct(ContactLead $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /***
         * gửi thông tin contact lên server
         *
         */
        /***
         * @var $contactPushRes ContactPushEloquentRepository
         */
        $contactPushRes = app()->make(ContactPushRepositoryInterface::class);

        $contactPushRes->handler($this->contact);

    }
}
