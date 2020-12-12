<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class FixtureNamespacedParamReturn
{
    /**
     * @param PropertyFetch $propertyFetch
     */
    function foo(PropertyFetch $propertyFetch)
    {
        return $propertyFetch;
    }
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class FixtureNamespacedParamReturn
{
    function foo(PropertyFetch $propertyFetch)
    {
        return $propertyFetch;
    }
}

?>
