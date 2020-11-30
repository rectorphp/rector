<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;

class SkipSomeClassInvalidType
{
    /**
     * @param \stdClass $node
     */
    public function run(Node $node)
    {

    }
}

?>