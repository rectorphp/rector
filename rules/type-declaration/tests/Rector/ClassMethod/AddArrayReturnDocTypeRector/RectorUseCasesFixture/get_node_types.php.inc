<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\RectorUseCasesFixture;

use PhpParser\Node\Expr\BinaryOp\BooleanOr;

class GetNodeTypes
{
    public function getNodeTypes(): array
    {
        return [BooleanOr::class];
    }
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\RectorUseCasesFixture;

use PhpParser\Node\Expr\BinaryOp\BooleanOr;

class GetNodeTypes
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [BooleanOr::class];
    }
}
?>
