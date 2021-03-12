<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

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
