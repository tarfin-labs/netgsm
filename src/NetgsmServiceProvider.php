<?php

namespace TarfinLabs\Netgsm;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use TarfinLabs\Netgsm\Exceptions\InvalidConfiguration;

class NetgsmServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('netgsm.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'netgsm');

        $this->app->singleton(Netgsm::class, function ($app) {
            $config = config('netgsm');

            if (is_null($config)) {
                throw InvalidConfiguration::configurationNotSet();
            }

            return new Netgsm(new Client(), $config['credentials'], $config['defaults']);
        });
    }
}
