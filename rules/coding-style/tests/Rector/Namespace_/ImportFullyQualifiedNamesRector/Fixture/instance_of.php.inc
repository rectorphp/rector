<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;

final class ImportPartialInstanceOf
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    public function isIteratorToArrayFuncCall(Expr $expr): bool
    {
        return $expr instanceof Node\Expr\FuncCall;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;

final class ImportPartialInstanceOf
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    public function isIteratorToArrayFuncCall(Expr $expr): bool
    {
        return $expr instanceof FuncCall;
    }
}

?>
