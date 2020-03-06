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
        $this->loadTranslationsFrom(dirname(__DIR__).'/resources/lang', 'netgsm');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__).'/resources/lang' => resource_path('lang/vendor/netgsm'),
            ]);

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

            $client = new Client([
                'base_uri' => $config['defaults']['base_uri'],
                'timeout'  => $config['defaults']['timeout'],
            ]);

            return new Netgsm($client, $config['credentials'], $config['defaults']);
        });
    }
}
