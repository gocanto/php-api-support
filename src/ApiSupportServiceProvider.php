<?php

declare(strict_types=1);

namespace YQueue\ApiSupport;

use Illuminate\Support\ServiceProvider;

final class ApiSupportServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        // Load our configuration
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/api-support.php', 'api-support');
        }
    }
}
