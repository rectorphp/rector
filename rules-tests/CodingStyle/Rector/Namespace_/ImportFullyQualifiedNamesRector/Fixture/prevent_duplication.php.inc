<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use PhpParser\Node;

class PreventDuplication
{
    public function getNodeTypes(): array
    {
        return [Node\Stmt\Expression::class];
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use PhpParser\Node\Stmt\Expression;
use PhpParser\Node;

class PreventDuplication
{
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }
}

?>
