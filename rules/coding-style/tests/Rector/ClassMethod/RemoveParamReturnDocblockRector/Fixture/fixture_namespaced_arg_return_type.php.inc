<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class SomeClassNamespacedArgParamReturnFQCN
{
    /**
     * @param PropertyFetch $propertyFetch
     * @return PropertyFetch
     */
    function foo(\PhpParser\Node\Expr\PropertyFetch $propertyFetch): \PhpParser\Node\Expr\PropertyFetch
    {
        return $propertyFetch;
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class SomeClassNamespacedArgParamReturnFQCN
{
    function foo(\PhpParser\Node\Expr\PropertyFetch $propertyFetch): \PhpParser\Node\Expr\PropertyFetch
    {
        return $propertyFetch;
    }
}

?>
