<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;

final class DirectReturn
{
    public function run(Expr $expr, Expr $expr2)
    {
        return new BooleanOr($expr, $expr2);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;

final class DirectReturn
{
    public function run(Expr $expr, Expr $expr2): BooleanOr
    {
        return new BooleanOr($expr, $expr2);
    }
}

?>
