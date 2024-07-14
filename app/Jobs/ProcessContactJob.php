<?php

namespace App\Jobs;

use App\Models\ContactLead;
use App\Repositories\Traffic\TrafficRepository;
use App\Repositories\Contact\ContactRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessContactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var ContactLead
     */
    private $contactLead;

    /***
     * ProcessContactJob constructor.
     *
     * @param ContactLead $contactLead
     */
    public function __construct(ContactLead $contactLead)
    {
        $this->contactLead = $contactLead;
    }

    /***
     * @param ContactRepositoryInterface | TrafficRepository $contactRepository
     */
    public function handle(ContactRepositoryInterface $contactRepository)
    {
        $contactRepository->process($this->contactLead);
    }
}
