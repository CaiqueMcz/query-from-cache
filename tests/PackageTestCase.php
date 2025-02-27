<?php

namespace CaiqueMcz\QueryFromCache\Tests;

use Orchestra\Testbench\TestCase;

abstract class PackageTestCase extends TestCase
{
    /**
     * Registra o Service Provider do pacote.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \CaiqueMcz\QueryFromCache\QueryFromCacheServiceProvider::class,
        ];
    }

    /**
     * Configura o ambiente para os testes.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('query-from-cache', [
            'prefix'     => 'prefix_',
            'expires_in' => 30,
            'cache_class' => \Illuminate\Support\Facades\Cache::class,
        ]);
    }
}
