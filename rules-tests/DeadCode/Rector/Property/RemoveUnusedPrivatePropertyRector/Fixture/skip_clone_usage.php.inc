<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipCloneUsage
{
    private $var;

    public function __construct($var)
    {
        $this->var = $var;
    }

    public function run()
    {
        $obj = clone $this;
        echo $obj->var;
    }
}
