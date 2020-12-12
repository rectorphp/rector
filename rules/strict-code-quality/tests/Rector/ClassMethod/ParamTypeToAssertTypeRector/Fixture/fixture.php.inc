<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;

class Fixture
{
    /**
     * @param PropertyFetch|StaticPropertyFetch $node
     */
    public function run(Node $node)
    {

    }
}

?>
-----
<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;

class Fixture
{
    /**
     * @param PropertyFetch|StaticPropertyFetch $node
     */
    public function run(Node $node)
    {
        \Webmozart\Assert\Assert::isAnyOf($node, [\PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\StaticPropertyFetch::class]);
    }
}

?>
