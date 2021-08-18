<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;

final class TernaryReturn
{
    public function run(Expr $expr, Expr $expr2): BooleanAnd | BooleanOr
    {
        return rand(0, 1)
            ? new BooleanAnd($expr, $expr2)
            : new BooleanOr($expr, $expr2);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;

final class TernaryReturn
{
    public function run(Expr $expr, Expr $expr2): BooleanAnd | BooleanOr
    {
        return rand(0, 1) !== 0
            ? new BooleanAnd($expr, $expr2)
            : new BooleanOr($expr, $expr2);
    }
}

?>
