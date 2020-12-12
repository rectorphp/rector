<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;

class SkipSameParamTypeArgNotFqcn
{
    /**
     * @param PropertyFetch $propertyFetch
     */
    public function run(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {

    }
}

?>
