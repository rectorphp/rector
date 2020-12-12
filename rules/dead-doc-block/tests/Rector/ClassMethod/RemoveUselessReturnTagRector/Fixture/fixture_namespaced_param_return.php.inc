<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class SomeClassNamespacedParamReturn
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

class SomeClassNamespacedParamReturn
{
    function foo(PropertyFetch $propertyFetch): PropertyFetch
    {
        return $propertyFetch;
    }
}

?>
