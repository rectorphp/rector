<?php

namespace Rector\CodeQualityStrict\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;

class SkipSomeClassSameTypeArg
{
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch $propertyFetch
     */
    public function run(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {

    }
}

?>
