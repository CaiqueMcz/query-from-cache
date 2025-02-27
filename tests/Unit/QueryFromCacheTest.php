<?php

namespace CaiqueMcz\QueryFromCache\Tests\Unit;

use CaiqueMcz\QueryFromCache\QueryFromCache;
use CaiqueMcz\QueryFromCache\Enums\CacheAction;
use CaiqueMcz\QueryFromCache\Tests\PackageTestCase;
use Illuminate\Support\Facades\Cache;
use Mockery;

class QueryFromCacheTest extends PackageTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetValidMethodReturnsArray(): void
    {
        $methodName = 'findFromCache';
        $result = QueryFromCache::getValidMethod($methodName);
        $this->assertIsArray($result);
        $this->assertEquals(['find', 'from', 'cache'], $result);
    }

    public function testGetValidMethodReturnsNullForInvalidMethod(): void
    {
        $methodName = 'findCache';
        $result = QueryFromCache::getValidMethod($methodName);
        $this->assertNull($result);
    }

    public function testGetActionDefault(): void
    {
        $model = new DummyModel();
        $query = new QueryFromCache('findFromCache', [], $model);
        $action = $query->getAction([]);
        $this->assertEquals(CacheAction::CREATE(), $action);
    }

    public function testGetActionProvided(): void
    {
        $model = new DummyModel();
        $params = [30, CacheAction::REFRESH()];
        $query = new QueryFromCache('findFromCache', $params, $model);
        $action = $query->getAction($params);
        $this->assertEquals(CacheAction::REFRESH(), $action);
    }

    public function testGetExpiresInParameter(): void
    {
        $model = new DummyModel();
        $params = [45];
        $query = new QueryFromCache('findFromCache', $params, $model);
        $this->assertEquals(45, $query->getExpiresIn($params));
    }

    public function testGetExpiresInDefault(): void
    {
        $model = new DummyModel();
        $params = [];
        config(['query-from-cache.expires_in' => 30]);
        $query = new QueryFromCache('findFromCache', $params, $model);
        $this->assertEquals(30, $query->getExpiresIn($params));
    }

    public function testGetCacheKeyGeneration(): void
    {
        $model = new DummyModel();
        config(['query-from-cache.prefix' => 'prefix_']);
        $query = new QueryFromCache('findFromCache', [], $model);
        $expectedKey = 'prefix_' . 'find' . ':' . $model->getTable() . ':' . $model->getKey();
        $this->assertEquals($expectedKey, $query->getCacheKey());
    }

    public function testGetCacheOrCreateStoresAndRetrievesValue()
    {
        $model = new DummyModel();
        config(['query-from-cache.prefix' => 'prefix_']);
        $query = new QueryFromCache('findFromCache', [30], $model);
        $cacheKey = $query->getCacheKey();

        Cache::shouldReceive('has')
            ->once()
            ->with($cacheKey)
            ->andReturn(false);

        Cache::shouldReceive('put')
            ->once()
            ->with($cacheKey, 'computed_result', Mockery::type('DateTimeInterface'))
            ->andReturnNull();

        Cache::shouldReceive('get')
            ->once()
            ->with($cacheKey)
            ->andReturn('computed_result');

        $result = $query->getCacheOrCreate($cacheKey, $model, function ($m) {
            return 'computed_result';
        }, 30);

        $this->assertEquals('computed_result', $result);
    }

    public function testForgetRemovesCacheEntry(): void
    {
        $model = new DummyModel();
        config(['query-from-cache.prefix' => 'prefix_']);
        $query = new QueryFromCache('findFromCache', [], $model);
        $cacheKey = $query->getCacheKey();

        Cache::shouldReceive('forget')
            ->once()
            ->with($cacheKey)
            ->andReturn(true);

        $this->assertTrue($query->forget());
    }

    public function testGetMethodWithCreateAction(): void
    {
        $model = new class extends DummyModel {
            public $callCount = 0;
            public function find()
            {
                $this->callCount++;
                return 'found_result_' . $this->callCount;
            }
        };

        config(['query-from-cache.prefix' => 'prefix_']);
        $query = new QueryFromCache('findFromCache', [30], $model);
        $cacheKey = $query->getCacheKey();

        // Primeira chamada: cache não possui o valor
        Cache::shouldReceive('has')
            ->once()
            ->with($cacheKey)
            ->andReturn(false);
        Cache::shouldReceive('put')
            ->once()
            ->with($cacheKey, 'found_result_1', Mockery::type('DateTimeInterface'))
            ->andReturnNull();
        Cache::shouldReceive('get')
            ->once()
            ->with($cacheKey)
            ->andReturn('found_result_1');

        $result1 = $query->get();
        $this->assertEquals('found_result_1', $result1);
        $this->assertEquals(1, $model->callCount);

        // Segunda chamada: valor em cache
        Cache::shouldReceive('has')
            ->once()
            ->with($cacheKey)
            ->andReturn(true);
        Cache::shouldReceive('get')
            ->once()
            ->with($cacheKey)
            ->andReturn('found_result_1');

        $result2 = $query->get();
        $this->assertEquals('found_result_1', $result2);
        $this->assertEquals(1, $model->callCount);
    }

    public function testGetMethodWithForgetAction(): void
    {
        $model = new DummyModel();
        config(['query-from-cache.prefix' => 'prefix_']);
        $params = [30, CacheAction::FORGET()];
        $query = new QueryFromCache('findFromCache', $params, $model);
        $cacheKey = $query->getCacheKey();

        Cache::shouldReceive('forget')
            ->once()
            ->with($cacheKey)
            ->andReturn(true);


        $result = $query->get();

        $this->assertTrue($result);
    }

    public function testGetMethodWithRefreshAction(): void
    {
        $model = new DummyModel();
        config(['query-from-cache.prefix' => 'prefix_']);
        $params = [30, CacheAction::REFRESH()];
        $query = new QueryFromCache('findFromCache', $params, $model);
        $cacheKey = $query->getCacheKey();

        // Para a ação REFRESH, espera-se que o cache seja limpo e recriado
        Cache::shouldReceive('forget')
            ->once()
            ->with($cacheKey)
            ->andReturn(true);

        Cache::shouldReceive('has')
            ->once()
            ->with($cacheKey)
            ->andReturn(false);
        Cache::shouldReceive('put')
            ->once()
            ->with($cacheKey, 'result', Mockery::type('DateTimeInterface'))
            ->andReturnNull();
        Cache::shouldReceive('get')
            ->once()
            ->with($cacheKey)
            ->andReturn('result');

        $result = $query->get();
        $this->assertEquals('result', $result);
    }
}
