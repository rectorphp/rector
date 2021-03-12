<?php

namespace Rector\Tests\CodeQualityStrict\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;

class SkipSomeClassSameTypeArgNotFQCN
{
    /**
     * @param PropertyFetch $propertyFetch
     */
    public function run(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {

    }
}

?>
