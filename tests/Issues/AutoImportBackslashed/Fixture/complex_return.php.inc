<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;

final class ComplexReturn
{
    public function run(Expr $expr, Expr $expr2)
    {
        if (true) {
            return rand(0, 1) !== 0
                ? new BooleanAnd($expr, $expr2)
                : new BooleanOr($expr, $expr2);
        }

        if (false) {
            return new BooleanNot($expr);
        }

        return new Identical($expr, $expr);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;

final class ComplexReturn
{
    public function run(Expr $expr, Expr $expr2): BooleanAnd|BooleanOr|Identical|BooleanNot
    {
        if (true) {
            return rand(0, 1) !== 0
                ? new BooleanAnd($expr, $expr2)
                : new BooleanOr($expr, $expr2);
        }

        if (false) {
            return new BooleanNot($expr);
        }

        return new Identical($expr, $expr);
    }
}

?>
