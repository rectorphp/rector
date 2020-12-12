<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class FixtureNamespacedParamReturn
{
    /**
     * @return PropertyFetch
     */
    function foo(PropertyFetch $propertyFetch): PropertyFetch
    {
        return $propertyFetch;
    }
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class FixtureNamespacedParamReturn
{
    function foo(PropertyFetch $propertyFetch): PropertyFetch
    {
        return $propertyFetch;
    }
}

?>
