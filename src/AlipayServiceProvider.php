<?php

namespace Cncal\Alipay;

use Illuminate\Support\ServiceProvider;

class AlipayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/alipay.php' => config_path('alipay.php'),
            __DIR__.'/../storage/logs/alipay.log' => storage_path('logs/alipay.log'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('alipay', function() {
            return new Alipay;
        });
    }
}
