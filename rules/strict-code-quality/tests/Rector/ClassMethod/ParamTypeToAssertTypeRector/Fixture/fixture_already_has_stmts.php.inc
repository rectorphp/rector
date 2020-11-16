<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;

class SomeClassAlreadyHasStmts
{
    /**
     * @param PropertyFetch|StaticPropertyFetch $node
     */
    public function run(Node $node)
    {
        echo 'test';
    }
}

?>
-----
<?php

namespace Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;

class SomeClassAlreadyHasStmts
{
    public function run(Node $node)
    {
        \Webmozart\Assert\Assert::isAnyOf($node, [PropertyFetch::class, StaticPropertyFetch::class]);
        echo 'test';
    }
}

?>
