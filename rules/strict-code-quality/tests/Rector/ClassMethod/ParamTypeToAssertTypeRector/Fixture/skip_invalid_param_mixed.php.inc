<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;

class SkipInvalidParamMixed
{
    /**
     * @param PropertyFetch|string $node
     */
    public function run($node)
    {

    }
}

?>