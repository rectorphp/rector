<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class SomeClass3
{
    private $x;
    private $y;

    public function run()
    {
        isset($this->x, $this->y);
    }
}
