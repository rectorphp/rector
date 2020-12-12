<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class FixtureNamespacedArgReturnType
{
    /**
     * @param PropertyFetch $propertyFetch
     */
    function foo(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {
        return $propertyFetch;
    }
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class FixtureNamespacedArgReturnType
{
    function foo(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {
        return $propertyFetch;
    }
}

?>
