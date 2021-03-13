<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;

final class ReturnStringType
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;

final class ReturnStringType
{
    /**
     * @return array<class-string<\PhpParser\Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
}

?>
