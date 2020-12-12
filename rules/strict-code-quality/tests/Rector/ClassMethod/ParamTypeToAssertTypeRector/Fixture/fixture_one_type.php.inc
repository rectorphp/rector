<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;

class FixtureOneType
{
    /**
     * @param PropertyFetch $node
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

class FixtureOneType
{
    /**
     * @param PropertyFetch $node
     */
    public function run(Node $node)
    {
        \Webmozart\Assert\Assert::isAOf($node, \PhpParser\Node\Expr\PropertyFetch::class);
    }
}

?>
