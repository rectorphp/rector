<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;

class SkipSameParamTypeNotFqcn
{
    /**
     * @param PropertyFetch $propertyFetch
     */
    public function run(PropertyFetch $propertyFetch)
    {

    }
}

?>
