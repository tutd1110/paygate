<?php

namespace App\Jobs;

use App\Models\ContactLeadProcess;
use App\Repositories\ZNS\ZnsInterface;
use App\Repositories\ZNS\ZnsRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushZnsHocMai implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $contactLeadProcess;
    public $event;

    public function __construct(ContactLeadProcess $contactLeadProcess, $event = null)
    {
        $this->contactLeadProcess = $contactLeadProcess->toArray();
        $this->event = $event;
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
        $this->znsRepository = app()->make(ZnsInterface::class);
        $this->znsRepository->sendZnsFpt($this->contactLeadProcess,$this->event);
    }
}
