<?php

namespace Yexk\LokiLogger;

use Illuminate\Support\ServiceProvider;

class LokiLoggerServiceProvider extends ServiceProvider
{
    public const LOG_LOCATION = 'logs/loki.log';

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/lokiloggerconfig.php' => config_path('lokiloggerconfig.php')
        ], 'laravel-loki-logger');
        $this->commands([
            LokiLoggerPersister::class
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/lokiloggerconfig.php',
            'lokilogging'
        );
    }
}
