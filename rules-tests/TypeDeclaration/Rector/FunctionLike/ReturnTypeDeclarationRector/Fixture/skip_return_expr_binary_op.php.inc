<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Scalar\DNumber;

final class SkipReturnExprBinaryOp
{
    public function run($isNegated, Expr $expr, DNumber $dNumber): BinaryOp
    {
        if ($isNegated) {
            return new Identical($expr, $dNumber);
        }

        return new NotIdentical($expr, $dNumber);
    }
}
