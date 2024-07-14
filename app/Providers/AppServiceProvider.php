<?php

namespace App\Providers;

use App\Repositories\Contact\ContactEloquentRepository;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Coupon\CouponRepositoryInterface;
use App\Repositories\Invoice\InvoiceInterface;
use App\Repositories\Invoice\InvoiceRepository;
use App\Repositories\PGW\PgwOrderRepository;
use App\Repositories\PGW\PgwOrderInterface;
use App\Repositories\RtaProduct\RtaProductRepository;
use App\Repositories\RtaProduct\RtaProductRepositoryInterface;
use App\Repositories\Traffic\TrafficRepository;
use App\Repositories\Contact\ContactPushEloquentRepository;
use App\Repositories\Contact\ContactPushRepositoryInterface;
use App\Repositories\Contact\ContactRepositoryInterface;
use App\Repositories\Traffic\TrafficRepositoryInterface;
use App\Repositories\UserBuy\UserBuyRepository;
use App\Repositories\UserBuy\UserBuyRepositoryInterface;
use App\Repositories\ZNS\ZnsInterface;
use App\Repositories\ZNS\ZnsRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        /***
         * boot repository
         */
        app()->singleton(ContactRepositoryInterface::class, ContactEloquentRepository::class);
        app()->singleton(ContactPushRepositoryInterface::class, ContactPushEloquentRepository::class);
        app()->singleton(TrafficRepositoryInterface::class, TrafficRepository::class);
        app()->singleton(CouponRepositoryInterface::class, CouponRepository::class);
        app()->singleton(RtaProductRepositoryInterface::class, RtaProductRepository::class);
        app()->singleton(UserBuyRepositoryInterface::class, UserBuyRepository::class);
        app()->singleton(InvoiceInterface::class, InvoiceRepository::class);
        app()->singleton(PgwOrderInterface::class, PgwOrderRepository::class);
        app()->singleton(ZnsInterface::class, ZnsRepository::class);
    }
}
