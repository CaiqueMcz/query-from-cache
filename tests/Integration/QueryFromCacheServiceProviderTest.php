<?php

namespace CaiqueMcz\QueryFromCache\Tests\Integration;

use CaiqueMcz\QueryFromCache\QueryFromCacheServiceProvider;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase;

class QueryFromCacheServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [QueryFromCacheServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('query-from-cache', [
            'prefix'     => 'prefix_',
            'expires_in' => 30,
            'cache_class' => Cache::class,
        ]);
    }

    public function testConfigurationIsMerged(): void
    {
        $this->assertNotNull(config('query-from-cache'));
        $this->assertArrayHasKey('prefix', config('query-from-cache'));
    }
}
