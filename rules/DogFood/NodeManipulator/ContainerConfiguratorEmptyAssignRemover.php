<?php

declare (strict_types=1);
namespace Rector\DogFood\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
final class ContainerConfiguratorEmptyAssignRemover
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer
     */
    private $exprUsedInNextNodeAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, ExprUsedInNextNodeAnalyzer $exprUsedInNextNodeAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->exprUsedInNextNodeAnalyzer = $exprUsedInNextNodeAnalyzer;
    }
    public function removeFromClosure(Closure $closure) : void
    {
        foreach ($closure->getStmts() as $key => $stmt) {
            if (!$this->isHelperAssign($stmt)) {
                continue;
            }
            /** @var Expression $expression */
            $expression = $closure->stmts[$key];
            /** @var Assign $assign */
            $assign = $expression->expr;
            /** @var Expr $var */
            $var = $assign->var;
            if ($this->exprUsedInNextNodeAnalyzer->isUsed($var)) {
                continue;
            }
            unset($closure->stmts[$key]);
        }
    }
    /**
     * Remove helper methods calls like:
     * $services = $containerConfigurator->services();
     * $parameters = $containerConfigurator->parameters();
     */
    private function isHelperAssign(Stmt $stmt) : bool
    {
        if (!$stmt instanceof Expression) {
            return \false;
        }
        $expression = $stmt->expr;
        if (!$expression instanceof Assign) {
            return \false;
        }
        if (!$expression->expr instanceof MethodCall) {
            return \false;
        }
        $methodCall = $expression->expr;
        return $this->nodeNameResolver->isNames($methodCall->name, ['parameters', 'services']);
    }
}
