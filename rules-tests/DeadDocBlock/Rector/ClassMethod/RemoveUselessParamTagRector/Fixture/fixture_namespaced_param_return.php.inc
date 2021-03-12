<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class SomeClassNamespacedParamReturn
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

class SomeClassNamespacedParamReturn
{
    function foo(PropertyFetch $propertyFetch)
    {
        return $propertyFetch;
    }
}

?>
