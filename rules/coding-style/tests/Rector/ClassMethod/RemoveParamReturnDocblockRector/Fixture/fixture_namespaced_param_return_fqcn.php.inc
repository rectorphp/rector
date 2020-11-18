<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\Fixture;

use PhpParser\Node\Expr\PropertyFetch;

class SomeClassNamespacedParamReturnFQCN
{
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch $propertyFetch
     * @return \PhpParser\Node\Expr\PropertyFetch
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

class SomeClassNamespacedParamReturnFQCN
{
    function foo(PropertyFetch $propertyFetch): PropertyFetch
    {
        return $propertyFetch;
    }
}

?>
