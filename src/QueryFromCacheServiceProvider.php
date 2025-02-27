<?php

namespace CaiqueMcz\QueryFromCache;

use Illuminate\Support\ServiceProvider;

class QueryFromCacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/query-from-cache.php',
            'query-from-cache'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/query-from-cache.php' => config_path('query-from-cache.php'),
        ], 'config');
    }
}
