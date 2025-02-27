<?php

namespace CaiqueMcz\QueryFromCache\Enums;

use MyCLabs\Enum\Enum;

/**
 * Cache Action
 *
 * @method static self CREATE()
 * @method static self REFRESH()
 * @method static self FORGET()
 */
class CacheAction extends Enum
{
    private const CREATE = 'CREATE';
    private const REFRESH = 'REFRESH';
    private const FORGET = 'FORGET';
}
