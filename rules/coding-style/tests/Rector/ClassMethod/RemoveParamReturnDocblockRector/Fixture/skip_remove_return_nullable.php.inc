<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use stdClass;

class SomeClassReturnNullable
{
    /**
     * @return stdClass|null
     */
    function foo(string $a, string $b, string $c): ?stdClass
    {

    }
}

?>
