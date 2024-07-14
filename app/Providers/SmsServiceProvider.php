<?php

namespace App\Providers;

use App\Lib\HocMaiSmsApi;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $hocmaiSms = new HocMaiSmsApi();
        app()->singleton('hocmai_sms', function () use ($hocmaiSms) {
            return $hocmaiSms;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
