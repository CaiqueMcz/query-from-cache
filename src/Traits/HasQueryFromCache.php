<?php

namespace CaiqueMcz\QueryFromCache\Traits;

use CaiqueMcz\QueryFromCache\QueryFromCache;
use Illuminate\Database\Eloquent\Model;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait HasQueryFromCache
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __call($method, $parameters)
    {
        $validMethod = QueryFromCache::getValidMethod($method);
        if (!is_null($validMethod)) {
            return (new QueryFromCache($method, $parameters, $this))->get();
        }
        return is_callable(['parent', '__call']) ? parent::__call($method, $parameters) : null;
    }
}
