<?php

declare(strict_types=1);

namespace Rector\Nette\Contract;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Nette\ValueObject\ContentExprAndNeedleExpr;

interface WithFunctionToNetteUtilsStringsRectorInterface
{
    public function getMethodName(): string;

    public function matchContentAndNeedleOfSubstrOfVariableLength(
        Node $node,
        Variable $variable
    ): ?ContentExprAndNeedleExpr;
}
