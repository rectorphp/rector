<?php

declare (strict_types=1);
namespace Rector\Defluent\Contract\ValueObject;

use PhpParser\Node\Expr;
interface RootExprAwareInterface
{
    public function getRootExpr() : \PhpParser\Node\Expr;
}
