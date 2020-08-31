<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

final class KeepAnonymousExtendedClass
{
    public function getNodeVisitor(): NodeVisitorAbstract
    {
        return new class extends NodeVisitorAbstract
        {
        };
    }
}
