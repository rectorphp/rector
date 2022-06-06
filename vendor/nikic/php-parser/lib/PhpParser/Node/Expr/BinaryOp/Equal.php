<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp;

use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp;
class Equal extends BinaryOp
{
    public function getOperatorSigil() : string
    {
        return '==';
    }
    public function getType() : string
    {
        return 'Expr_BinaryOp_Equal';
    }
}
