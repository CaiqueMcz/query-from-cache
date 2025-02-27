<?php

namespace CaiqueMcz\QueryFromCache\Tests\Integration;

use CaiqueMcz\QueryFromCache\Traits\HasQueryFromCache;
use Illuminate\Database\Eloquent\Model;

class DummyModelWithTrait extends Model
{
    use HasQueryFromCache;

    protected $table = 'dummy';

    public function getKey()
    {
        return 1;
    }

    // Método real que será chamado via __call
    public function find()
    {
        return 'trait_result';
    }
}
