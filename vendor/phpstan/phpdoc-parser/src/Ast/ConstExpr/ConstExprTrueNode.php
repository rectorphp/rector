<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\PhpDocParser\Ast\ConstExpr;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\NodeAttributes;
class ConstExprTrueNode implements ConstExprNode
{
    use NodeAttributes;
    public function __toString() : string
    {
        return 'true';
    }
}
