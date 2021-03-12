<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

final class SkipReturnAbstractClassType
{
    public function create(): NodeVisitor
    {
        return new class extends NodeVisitorAbstract
        {
        };
    }
}
