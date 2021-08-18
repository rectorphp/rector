<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;

final class ElseTernaryUnion
{
    public function run(?Expr $expr, Expr $expr2, Expr $expr3)
    {
        return $expr ? $expr : $this->getOther();
    }

    public function getOther(): string|int
    {
        return rand(0, 1)
            ? 'a'
            : 1;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Fixture;

use PhpParser\Node\Expr;

final class ElseTernaryUnion
{
    public function run(?Expr $expr, Expr $expr2, Expr $expr3): int|Expr|string
    {
        return $expr !== null ? $expr : $this->getOther();
    }

    public function getOther(): string|int
    {
        return rand(0, 1) !== 0
            ? 'a'
            : 1;
    }
}

?>