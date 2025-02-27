<?php

namespace CaiqueMcz\QueryFromCache\Tests\Unit;

use Illuminate\Database\Eloquent\Model;

class DummyModel extends Model
{
    protected $table = 'dummy';

    public function getKey()
    {
        return 1;
    }

    // Método que será chamado via QueryFromCache (ex.: "find")
    public function find()
    {
        return 'result';
    }
}
