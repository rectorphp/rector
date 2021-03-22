<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class SomeClassNamespacedArgParamReturnFQCN
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

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class SomeClassNamespacedArgParamReturnFQCN
{
    function foo(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {
        return $propertyFetch;
    }
}

?>
