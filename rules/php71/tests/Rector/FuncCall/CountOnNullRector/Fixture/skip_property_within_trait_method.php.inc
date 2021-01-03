<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

trait SkipPropertyWithinTraitMethod
{
    /**
     * @var array
     */
    private $array = [];

    public function run()
    {
        return count($this->array);
    }
}
