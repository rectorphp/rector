<?php

namespace Rector\CodeQualityStrict\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;

class SkipSomeClassSameType
{
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch $propertyFetch
     */
    public function run(PropertyFetch $propertyFetch)
    {

    }
}

?>
