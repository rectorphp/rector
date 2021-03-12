<?php

namespace Rector\Tests\CodeQualityStrict\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;

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
