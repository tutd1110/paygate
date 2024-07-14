<?php

namespace App\Providers;

use App\Lib\HocMaiShortLink;
use Illuminate\Support\ServiceProvider;

class ShortLinkServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('hocmai_short_link', HocMaiShortLink::class);
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
