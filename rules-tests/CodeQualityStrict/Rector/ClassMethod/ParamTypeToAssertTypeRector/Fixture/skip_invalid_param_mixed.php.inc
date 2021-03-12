<?php

namespace Rector\Tests\CodeQualityStrict\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;

class SkipSomeClassInvalidParamMixed
{
    /**
     * @param PropertyFetch|string $node
     */
    public function run($node)
    {

    }
}

?>
