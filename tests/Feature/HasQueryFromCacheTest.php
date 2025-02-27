<?php

namespace CaiqueMcz\QueryFromCache\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use CaiqueMcz\QueryFromCache\Tests\PackageTestCase;
use Mockery;

class HasQueryFromCacheTest extends PackageTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testValidMethodCallUsingTrait(): void
    {
        config(['query-from-cache.prefix' => 'prefix_']);
        $dummy = new DummyModelWithTrait();
        $cacheKey = config('query-from-cache.prefix') . 'find' . ':' . $dummy->getTable() . ':' . $dummy->getKey();

        Cache::shouldReceive('has')
            ->once()
            ->with($cacheKey)
            ->andReturn(false);
        Cache::shouldReceive('put')
            ->once()
            ->with($cacheKey, 'trait_result', \Mockery::type('DateTimeInterface'))
            ->andReturnNull();
        Cache::shouldReceive('get')
            ->once()
            ->with($cacheKey)
            ->andReturn('trait_result');

        $result = $dummy->findFromCache(30);
        $this->assertEquals('trait_result', $result);
    }

    public function testInvalidMethodCallFallsBack(): void
    {
        $dummy = new DummyModelWithTrait();
        $this->expectException(\BadMethodCallException::class);
        $dummy->nonExistentMethod();
    }
}
