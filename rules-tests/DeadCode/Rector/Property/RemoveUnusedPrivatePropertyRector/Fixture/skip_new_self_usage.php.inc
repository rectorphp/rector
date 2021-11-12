<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipNewSelfUsage
{
    private $var;
    private static $var2;

    public function __construct($var, $var2)
    {
        $this->var = $var;
        self::$var2 = $var2;
    }

    public function run()
    {
        $obj = new self('a', 'b');
        echo $obj->var;
        echo $obj::$var2;
    }
}
