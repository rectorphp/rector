<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;

class SkipSameParamTypeArg
{
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch $propertyFetch
     */
    public function run(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {

    }
}

?>
