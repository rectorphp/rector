<?php

namespace Rector\CodeQualityStrict\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;

class SomeClassMultiParam
{
    /**
     * @param PropertyFetch $node
     * @param StaticPropertyFetch $node2
     */
    public function run(Node $node, Node $node2)
    {

    }
}

?>
-----
<?php

namespace Rector\CodeQualityStrict\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;

class SomeClassMultiParam
{
    /**
     * @param PropertyFetch $node
     * @param StaticPropertyFetch $node2
     */
    public function run(Node $node, Node $node2)
    {
        \Webmozart\Assert\Assert::isAOf($node, \PhpParser\Node\Expr\PropertyFetch::class);
        \Webmozart\Assert\Assert::isAOf($node2, \PhpParser\Node\Expr\StaticPropertyFetch::class);
    }
}

?>
