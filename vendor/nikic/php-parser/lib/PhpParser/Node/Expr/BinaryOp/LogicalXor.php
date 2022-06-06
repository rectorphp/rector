<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp;

use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp;
class LogicalXor extends BinaryOp
{
    public function getOperatorSigil() : string
    {
        return 'xor';
    }
    public function getType() : string
    {
        return 'Expr_BinaryOp_LogicalXor';
    }
}
