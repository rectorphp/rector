<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Return_;

final class AssignAndRootExprAndNodesToAdd
{
    /**
     * @var array<Expr|Return_>
     */
    private $nodesToAdd = [];

    /**
     * @var AssignAndRootExpr
     */
    private $assignAndRootExpr;

    /**
     * @param array<Expr|Return_> $nodesToAdd
     */
    public function __construct(AssignAndRootExpr $assignAndRootExpr, array $nodesToAdd)
    {
        $this->assignAndRootExpr = $assignAndRootExpr;
        $this->nodesToAdd = $nodesToAdd;
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
