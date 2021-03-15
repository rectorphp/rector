<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;

final class SkipUnionExpr
{
    public function unwrapExpression(Stmt $stmt): Node
    {
        if ($stmt instanceof Expression) {
            return $stmt->expr;
        }

        return $stmt;
    }
}
