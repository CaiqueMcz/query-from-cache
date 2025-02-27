<?php

namespace CaiqueMcz\QueryFromCache;

use CaiqueMcz\QueryFromCache\Enums\CacheAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class QueryFromCache
{
    private ?array $parameters;
    private Model $model;
    private ?string $realMethodName;
    private string $cacheClass;

    public function __construct(string $methodName, ?array $parameters, Model $model, ?string $cacheClass = null)
    {
        $this->parameters = $parameters;
        $this->model = $model;
        $this->realMethodName = lcfirst(str_replace('FromCache', '', $methodName));
        if (is_null($cacheClass)) {
            $this->cacheClass = config('query-from-cache.cache_class');
        } else {
            $this->cacheClass = $cacheClass;
        }
    }

    public static function getValidMethod(string $methodName): ?array
    {
        $methodChunks = explode('_', Str::snake($methodName));
        if (count($methodChunks) < 3) {
            return null;
        }
        $lastParts = array_slice($methodChunks, -2);
        if ($lastParts[1] === 'cache' && $lastParts[0] === 'from' && count($methodChunks) > 2) {
            return $methodChunks;
        }
        return null;
    }

    public function hasKey()
    {
        return $this->model->getKey();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCacheOrCreate(string $cacheKey, Model $model, \Closure $callback, int $timeInMinutes = 3600)
    {
        $cacheClass = $this->getCacheClass();
        if ($cacheClass::has($cacheKey)) {
            return $cacheClass::get($cacheKey);
        }
        $expiresAt = now()->addMinutes($timeInMinutes);
        $cacheClass::put($cacheKey, $callback($model), $expiresAt);
        return $cacheClass::get($cacheKey);
    }

    public function getCacheClass()
    {
        return $this->cacheClass;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get()
    {
        $action = $this->getAction($this->parameters);
        if ($action->getValue() === CacheAction::FORGET()->getValue()) {
            return $this->forget();
        }
        if ($action->getValue() === CacheAction::REFRESH()->getValue()) {
            $this->forget();
        }
        $expiresIn = $this->getExpiresIn($this->parameters);
        $realMethodName = $this->getRealMethodName();
        $cacheKey = $this->getCacheKey();
        return $this->getCacheOrCreate($cacheKey, $this->model, function (Model $model) use ($realMethodName) {
            return $model->$realMethodName();
        }, $expiresIn);
    }

    public function getAction(?array $parameters): CacheAction
    {
        if (isset($parameters[1]) && $parameters[1] instanceof CacheAction) {
            return $parameters[1];
        }
        return CacheAction::CREATE();
    }

    public function forget(): bool
    {
        $cacheClass = $this->getCacheClass();
        return $cacheClass::forget($this->getCacheKey());
    }

    public function getCacheKey(): string
    {
        $prefix = config("query-from-cache.prefix");
        return $prefix . $this->getRealMethodName() . ':' . $this->model->getTable() . ':' . $this->model->getKey();
    }

    public function getRealMethodName(): ?string
    {
        return $this->realMethodName;
    }

    public function getExpiresIn(?array $parameters): int
    {
        if (isset($parameters[0]) && is_int($parameters[0]) && $parameters[0] > 0) {
            return $parameters[0];
        }
        return config('query-from-cache.expires_in', 30);
    }
}
