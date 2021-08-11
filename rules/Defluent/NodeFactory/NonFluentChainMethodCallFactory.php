<?php

declare (strict_types=1);
namespace Rector\Defluent\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeResolver\FirstMethodCallVarResolver;
use Rector\Defluent\ValueObject\AssignAndRootExpr;
use Rector\Defluent\ValueObject\FluentCallsKind;
use Rector\Naming\Naming\VariableNaming;
final class NonFluentChainMethodCallFactory
{
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    /**
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @var \Rector\Defluent\NodeResolver\FirstMethodCallVarResolver
     */
    private $firstMethodCallVarResolver;
    public function __construct(\Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer, \Rector\Naming\Naming\VariableNaming $variableNaming, \Rector\Defluent\NodeResolver\FirstMethodCallVarResolver $firstMethodCallVarResolver)
    {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->variableNaming = $variableNaming;
        $this->firstMethodCallVarResolver = $firstMethodCallVarResolver;
    }
    /**
     * @return Expression[]
     */
    public function createFromNewAndRootMethodCall(\PhpParser\Node\Expr\New_ $new, \PhpParser\Node\Expr\MethodCall $rootMethodCall) : array
    {
        $variableName = $this->variableNaming->resolveFromNode($new);
        if ($variableName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $newVariable = new \PhpParser\Node\Expr\Variable($variableName);
        $newStmts = [];
        $newStmts[] = $this->createAssignExpression($newVariable, $new);
        // resolve chain calls
        $chainMethodCalls = $this->fluentChainMethodCallNodeAnalyzer->collectAllMethodCallsInChainWithoutRootOne($rootMethodCall);
        $chainMethodCalls = \array_reverse($chainMethodCalls);
        foreach ($chainMethodCalls as $chainMethodCall) {
            $methodCall = new \PhpParser\Node\Expr\MethodCall($newVariable, $chainMethodCall->name, $chainMethodCall->args);
            $newStmts[] = new \PhpParser\Node\Stmt\Expression($methodCall);
        }
        return $newStmts;
    }
    /**
     * @param MethodCall[] $chainMethodCalls
     * @return Assign[]|Cast[]|MethodCall[]|Return_[]
     */
    public function createFromAssignObjectAndMethodCalls(\Rector\Defluent\ValueObject\AssignAndRootExpr $assignAndRootExpr, array $chainMethodCalls, string $kind, ?\PhpParser\Node $node = null) : array
    {
        $nodesToAdd = [];
        $isNewNodeNeeded = $this->isNewNodeNeeded($assignAndRootExpr);
        if ($isNewNodeNeeded) {
            $nodesToAdd[] = $assignAndRootExpr->createFirstAssign();
        }
        $decoupledMethodCalls = $this->createNonFluentMethodCalls($chainMethodCalls, $assignAndRootExpr, $isNewNodeNeeded);
        $nodesToAdd = \array_merge($nodesToAdd, $decoupledMethodCalls);
        if ($assignAndRootExpr->getSilentVariable() !== null && $kind !== \Rector\Defluent\ValueObject\FluentCallsKind::IN_ARGS) {
            $nodesToAdd[] = $assignAndRootExpr->getReturnSilentVariable();
        }
        if ($node instanceof \PhpParser\Node\Expr\Cast) {
            \end($nodesToAdd);
            $lastNodeToAdd = $nodesToAdd[\key($nodesToAdd)];
            $cast = \get_class($node);
            \end($nodesToAdd);
            $nodesToAdd[\key($nodesToAdd)] = new $cast($lastNodeToAdd);
        }
        return $nodesToAdd;
    }
    private function createAssignExpression(\PhpParser\Node\Expr\Variable $newVariable, \PhpParser\Node\Expr\New_ $new) : \PhpParser\Node\Stmt\Expression
    {
        $assign = new \PhpParser\Node\Expr\Assign($newVariable, $new);
        return new \PhpParser\Node\Stmt\Expression($assign);
    }
    private function isNewNodeNeeded(\Rector\Defluent\ValueObject\AssignAndRootExpr $assignAndRootExpr) : bool
    {
        if ($assignAndRootExpr->isFirstCallFactory()) {
            return \true;
        }
        if ($assignAndRootExpr->getRootExpr() === $assignAndRootExpr->getAssignExpr()) {
            return \false;
        }
        return $assignAndRootExpr->getRootExpr() instanceof \PhpParser\Node\Expr\New_;
    }
    /**
     * @param MethodCall[] $chainMethodCalls
     * @return Assign[]|MethodCall[]
     */
    private function createNonFluentMethodCalls(array $chainMethodCalls, \Rector\Defluent\ValueObject\AssignAndRootExpr $assignAndRootExpr, bool $isNewNodeNeeded) : array
    {
        $decoupledMethodCalls = [];
        \end($chainMethodCalls);
        $lastKey = \key($chainMethodCalls);
        foreach ($chainMethodCalls as $key => $chainMethodCall) {
            // skip first, already handled
            if ($key === $lastKey && $assignAndRootExpr->isFirstCallFactory() && $isNewNodeNeeded) {
                continue;
            }
            $chainMethodCall->var = $this->firstMethodCallVarResolver->resolve($assignAndRootExpr, $key);
            $decoupledMethodCalls[] = $chainMethodCall;
        }
        if ($assignAndRootExpr->getRootExpr() instanceof \PhpParser\Node\Expr\New_ && $assignAndRootExpr->getSilentVariable() !== null) {
            $decoupledMethodCalls[] = new \PhpParser\Node\Expr\Assign($assignAndRootExpr->getSilentVariable(), $assignAndRootExpr->getRootExpr());
        }
        return \array_reverse($decoupledMethodCalls);
    }
}
