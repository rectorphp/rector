<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;

final class TernaryReturn
{
    public function run(Expr $expr, Expr $expr2): int|BooleanAnd
    {
        return rand(0, 1)
            ? new BooleanAnd($expr, $expr2)
            : 1;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;

final class TernaryReturn
{
    public function run(Expr $expr, Expr $expr2): int|BooleanAnd
    {
        return rand(0, 1) !== 0
            ? new BooleanAnd($expr, $expr2)
            : 1;
    }
}

?>
