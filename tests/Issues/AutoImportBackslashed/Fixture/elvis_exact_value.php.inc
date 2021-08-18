<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;

final class ElvisExactValue
{
    public function run(Expr $expr, Expr $expr2)
    {
        return 'a' ? : new BooleanAnd($expr, $expr2);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;

final class ElvisExactValue
{
    public function run(Expr $expr, Expr $expr2): string
    {
        return 'a' ? : new BooleanAnd($expr, $expr2);
    }
}

?>
