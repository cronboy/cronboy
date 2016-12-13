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
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * @return void
     */
    public function boot()
    {
        //   config
        $this->publishes([__DIR__.'/../config/cronboy.php' => config_path('cronboy.php')], 'config');

        //   routes
        $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
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

        $this->app->singleton(CronboySaaS::class, function () {
            return new CronboySaaS(config('cronboy.token'), config('cronboy.app_key'));
        });

        $this->app->singleton(Cronboy::class, function ($app) {
            return new Cronboy(
                app(CronboySaaS::class), new SerializerService(config('cronboy.signature_key'))
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [CronboySaaS::class, Cronboy::class];
    }
}
