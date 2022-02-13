<?php

namespace App\Providers;

use App\Contracts\ExchangerInterface;
use App\Services\CoindeskService;
use App\Services\EcbService;
use App\Validations\ExchangerValidator;
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
