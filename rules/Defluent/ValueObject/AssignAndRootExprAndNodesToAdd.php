<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Return_;

final class AssignAndRootExprAndNodesToAdd
{
    /**
     * @param array<Expr|Return_> $nodesToAdd
     */
    public function __construct(
        private AssignAndRootExpr $assignAndRootExpr,
        private array $nodesToAdd
    ) {
    }

    /**
     * @return Expr[]|Return_[]
     */
    public function getNodesToAdd(): array
    {
        return $this->nodesToAdd;
    }

    public function getRootCallerExpr(): Expr
    {
        return $this->assignAndRootExpr->getCallerExpr();
    }
}
