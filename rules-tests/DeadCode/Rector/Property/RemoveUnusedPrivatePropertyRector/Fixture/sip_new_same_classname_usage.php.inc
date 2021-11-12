<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipNewSameClassNameUsage
{
    private $var;

    public function __construct($var)
    {
        $this->var = $var;
    }

    public function run($var)
    {
        $obj = new SkipNewSameClassNameUsage($var);
        echo $obj->var;
    }
}
