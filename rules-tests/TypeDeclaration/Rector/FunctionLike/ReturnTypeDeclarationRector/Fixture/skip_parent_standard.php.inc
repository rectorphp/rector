<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\Node\Scalar\String_;
use PhpParser\PrettyPrinter\Standard;

final class SkipParentStandard extends Standard
{
    protected function pExpr_Cast_String(String_ $node): string
    {
        return parent::pExpr_Cast_String($node);
    }
}
