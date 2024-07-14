<?php

namespace App\Jobs\PushDataFireBase;

use App\Helper\FireBaseHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushDataFireBase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 5;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $paramFirebaseNotification;
    public $urlFirebaseNotification;
    public function __construct($paramFirebaseNotification,$urlFirebaseNotification)
    {
        $this->paramFirebaseNotification = $paramFirebaseNotification;
        $this->urlFirebaseNotification = $urlFirebaseNotification;
        /***
         * gửi params thông báo thành công lên firebase theo đường dẫn
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
         * @var $fireBaseHelper FireBaseHelper
         */
        $this->fireBaseHelper = app()->make(FireBaseHelper::class);
        $this->fireBaseHelper->setDataFireBase($this->paramFirebaseNotification,$this->urlFirebaseNotification);
    }
}
