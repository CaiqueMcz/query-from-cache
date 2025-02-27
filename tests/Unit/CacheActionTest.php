<?php

namespace CaiqueMcz\QueryFromCache\Tests\Unit;

use CaiqueMcz\QueryFromCache\Enums\CacheAction;
use CaiqueMcz\QueryFromCache\Tests\PackageTestCase;

class CacheActionTest extends PackageTestCase
{
    public function testCacheActionValues(): void
    {
        $this->assertEquals('CREATE', CacheAction::CREATE()->getValue());
        $this->assertEquals('REFRESH', CacheAction::REFRESH()->getValue());
        $this->assertEquals('FORGET', CacheAction::FORGET()->getValue());
    }
}
