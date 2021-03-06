<?php

namespace App\Providers;

use App\Contracts\ExchangerInterface;
use App\Services\External\CoindeskService;
use App\Services\External\EcbService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app
            ->when(EcbService::class)
            ->needs(ExchangerInterface::class);
        $this->app
            ->when(CoindeskService::class)
            ->needs(ExchangerInterface::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
