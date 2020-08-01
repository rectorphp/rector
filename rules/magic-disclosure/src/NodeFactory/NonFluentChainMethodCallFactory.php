<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\MagicDisclosure\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;
use Rector\NetteKdyby\Naming\VariableNaming;

final class NonFluentChainMethodCallFactory
{
    /**
     * @var FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;

    /**
     * @var VariableNaming
     */
    private $variableNaming;

    public function __construct(
        FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer,
        VariableNaming $variableNaming
    ) {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->variableNaming = $variableNaming;
    }

    /**
     * @return Expression[]
     */
    public function createFromNewAndRootMethodCall(New_ $new, MethodCall $rootMethodCall): array
    {
        $variableName = $this->variableNaming->resolveFromNode($new);
        $newVariable = new Variable($variableName);

        $newStmts = [];
        $newStmts[] = $this->createAssignExpression($newVariable, $new);

        // resolve chain calls
        $chainMethodCalls = $this->fluentChainMethodCallNodeAnalyzer->collectAllMethodCallsInChainWithoutRootOne(
            $rootMethodCall
        );

        $chainMethodCalls = array_reverse($chainMethodCalls);
        foreach ($chainMethodCalls as $chainMethodCall) {
            $methodCall = new MethodCall($newVariable, $chainMethodCall->name, $chainMethodCall->args);
            $newStmts[] = new Expression($methodCall);
        }

        return $newStmts;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     */
    public function createFromAssignObjectAndMethodCalls(
        AssignAndRootExpr $assignAndRootExpr,
        array $chainMethodCalls,
        string $kind = 'normal'
    ): array {
        $nodesToAdd = [];

        if ($this->isNewNodeNeeded($assignAndRootExpr)) {
            $nodesToAdd[] = $assignAndRootExpr->getFirstAssign();
        }

        $decoupledMethodCalls = $this->createNonFluentMethodCalls($chainMethodCalls, $assignAndRootExpr);
        $nodesToAdd = array_merge($nodesToAdd, $decoupledMethodCalls);

        if ($assignAndRootExpr->getSilentVariable() !== null && $kind !== 'in_args') {
            $nodesToAdd[] = $assignAndRootExpr->getReturnSilentVariable();
        }

        return $nodesToAdd;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     * @return Expr[]
     */
    private function createNonFluentMethodCalls(array $chainMethodCalls, AssignAndRootExpr $assignAndRootExpr): array
    {
        $decoupledMethodCalls = [];

        foreach ($chainMethodCalls as $chainMethodCall) {
            $chainMethodCall->var = $assignAndRootExpr->getCallerExpr();
            $decoupledMethodCalls[] = $chainMethodCall;
        }

        if ($assignAndRootExpr->getRootExpr() instanceof New_ && $assignAndRootExpr->getSilentVariable() !== null) {
            $decoupledMethodCalls[] = new Assign(
                $assignAndRootExpr->getSilentVariable(),
                $assignAndRootExpr->getRootExpr()
            );
        }

        return array_reverse($decoupledMethodCalls);
    }

    private function isNewNodeNeeded(AssignAndRootExpr $assignAndRootExpr): bool
    {
        if (! $assignAndRootExpr->getRootExpr() instanceof New_) {
            return false;
        }

        return $assignAndRootExpr->getRootExpr() !== $assignAndRootExpr->getAssignExpr();
    }

    private function createAssignExpression(Variable $newVariable, New_ $new): Expression
    {
        $assign = new Assign($newVariable, $new);
        return new Expression($assign);
    }
}
