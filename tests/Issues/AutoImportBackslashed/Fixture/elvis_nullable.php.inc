<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;

final class ElvisNullable
{
    public function run(?Expr $expr, Expr $expr2, Expr $expr3)
    {
        return $expr ?: new BooleanOr($expr2, $expr3);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;

final class ElvisNullable
{
    public function run(?Expr $expr, Expr $expr2, Expr $expr3): Expr|BooleanOr
    {
        return $expr ?: new BooleanOr($expr2, $expr3);
    }
}

?>
