<?php

namespace Micrologs\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Client\Factory as HttpFactory;

class MicrologsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/micrologs.php',
            'micrologs'
        );

        $this->app->singleton(MicrologsClient::class, function ($app) {
            $config = $app['config']['micrologs'];

            return new MicrologsClient(
                host:    $config['host'],
                key:     $config['key'],
                http:    $app->make(HttpFactory::class),
                timeout: $config['timeout'] ?? 5,
            );
        });

        // Allow resolving by the interface alias too
        $this->app->alias(MicrologsClient::class, 'micrologs');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/micrologs.php' => config_path('micrologs.php'),
            ], 'micrologs-config');
        }
    }
}
?>