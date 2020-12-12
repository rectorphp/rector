<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class FixtureNamespacedArgReturnType
{
    /**
     * @return PropertyFetch
     */
    function foo(): \PhpParser\Node\Expr\PropertyFetch
    {
        return $propertyFetch;
    }
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class FixtureNamespacedArgReturnType
{
    function foo(): \PhpParser\Node\Expr\PropertyFetch
    {
        return $propertyFetch;
    }
}

?>
