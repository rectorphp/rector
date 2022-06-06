<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PhpParser\Node\Expr\AssignOp;

use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp;
class Div extends AssignOp
{
    public function getType() : string
    {
        return 'Expr_AssignOp_Div';
    }
}
