<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueDefaultValueRenameParam\Fixture;

use PhpParser\Node\Expr;

final class Fixture
{
    private function check(Expr $value)
    {
        return ! $this->valueResolver->isValue($value, 0);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueDefaultValueRenameParam\Fixture;

use PhpParser\Node\Expr;

final class Fixture
{
    private function check(Expr $expr)
    {
        return ! $this->valueResolver->isValue($expr, 0);
    }
}

?>
