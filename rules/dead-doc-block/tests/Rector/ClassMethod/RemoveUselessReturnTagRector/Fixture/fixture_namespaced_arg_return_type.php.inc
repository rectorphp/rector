<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class SomeClassNamespacedArgParamReturnFQCN
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

class SomeClassNamespacedArgParamReturnFQCN
{
    function foo(): \PhpParser\Node\Expr\PropertyFetch
    {
        return $propertyFetch;
    }
}

?>
