<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

class SomeClassNonClassNameArg
{
    /**
     * @param string[] $arg
     */
    public function run(array $arg)
    {

    }
}

?>