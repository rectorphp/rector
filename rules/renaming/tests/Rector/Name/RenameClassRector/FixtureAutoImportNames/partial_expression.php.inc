<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture\AutoImportNamesParameter;

use PhpParser\Node;

class PartialExpression
{
    public function getNodeTypes(): array
    {
        return [Node\Stmt\Expression::class];
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture\AutoImportNamesParameter;

use PhpParser\Node\Stmt\Expression;
use PhpParser\Node;

class PartialExpression
{
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }
}

?>
