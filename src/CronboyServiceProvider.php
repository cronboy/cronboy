<?php

namespace Cronboy\Cronboy;

use Cronboy\Cronboy\Client\CronboySaaS;
use Cronboy\Cronboy\Services\SerializerService;
use Illuminate\Support\ServiceProvider;

/**
 * Class CronboyServiceProvider.
 */
class CronboyServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        //   config
        $this->publishes([__DIR__.'/../config/cronboy.php' => config_path('cronboy.php')]);

        //   routes
        if (!$this->app->routesAreCached()) {
            require __DIR__.'/Http/routes.php';
        }
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cronboy.php', 'cronboy'
        );

        $this->app->singleton(Cronboy::class, function ($app) {
            return new Cronboy(
                new CronboySaaS(config('cronboy.api_token'), config('cronboy.app_key')), new SerializerService(config('cronboy.app_secret'))
            );
        });
    }
}
