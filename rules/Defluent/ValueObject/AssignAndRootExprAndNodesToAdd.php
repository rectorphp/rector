<?php

declare (strict_types=1);
namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Return_;
final class AssignAndRootExprAndNodesToAdd
{
    /**
     * @var \Rector\Defluent\ValueObject\AssignAndRootExpr
     */
    private $assignAndRootExpr;
    /**
     * @var \PhpParser\Node\Expr[]|\PhpParser\Node\Stmt\Return_[]
     */
    private $nodesToAdd;
    /**
     * @param array<Expr|Return_> $nodesToAdd
     */
    public function __construct(\Rector\Defluent\ValueObject\AssignAndRootExpr $assignAndRootExpr, array $nodesToAdd)
    {
        $this->assignAndRootExpr = $assignAndRootExpr;
        $this->nodesToAdd = $nodesToAdd;
    }
    /**
     * @return Expr[]|Return_[]
     */
    public function getNodesToAdd() : array
    {
        return $this->nodesToAdd;
    }
    public function getRootCallerExpr() : \PhpParser\Node\Expr
    {
        return $this->assignAndRootExpr->getCallerExpr();
    }
}
