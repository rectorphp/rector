<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;

final class SomeCoalesce
{
    public function run(?Expr $expr, Expr $expr2)
    {
        return $expr ?? new BooleanOr($expr, $expr2);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;

final class SomeCoalesce
{
    public function run(?Expr $expr, Expr $expr2): Expr|BooleanOr
    {
        return $expr ?? new BooleanOr($expr, $expr2);
    }
}

?>
