<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class SomeClassNamespacedParamReturn
{
    /**
     * @param PropertyFetch $propertyFetch
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

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class SomeClassNamespacedParamReturn
{
    function foo(PropertyFetch $propertyFetch): PropertyFetch
    {
        return $propertyFetch;
    }
}

?>
