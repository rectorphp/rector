<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;

class SkipSameParamType
{
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch $propertyFetch
     */
    public function run(PropertyFetch $propertyFetch)
    {

    }
}

?>
