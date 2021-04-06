<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;

class SkipReturnClassString
{
    /**
     * @return class-string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
}
