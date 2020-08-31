<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\MagicDisclosure\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\NodeManipulator\FluentChainMethodCallRootExtractor;
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
        if ($variableName === null) {
            throw new ShouldNotHappenException();
        }

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
     * @return Assign[]|\PhpParser\Node\Expr\MethodCall[]|Return_[]
     */
    public function createFromAssignObjectAndMethodCalls(
        AssignAndRootExpr $assignAndRootExpr,
        array $chainMethodCalls,
        string $kind = 'normal'
    ): array {
        $nodesToAdd = [];

        $isNewNodeNeeded = $this->isNewNodeNeeded($assignAndRootExpr);
        if ($isNewNodeNeeded) {
            $nodesToAdd[] = $assignAndRootExpr->createFirstAssign();
        }

        $decoupledMethodCalls = $this->createNonFluentMethodCalls(
            $chainMethodCalls,
            $assignAndRootExpr,
            $isNewNodeNeeded
        );

        $nodesToAdd = array_merge($nodesToAdd, $decoupledMethodCalls);

        if ($assignAndRootExpr->getSilentVariable() !== null && $kind !== FluentChainMethodCallRootExtractor::KIND_IN_ARGS) {
            $nodesToAdd[] = $assignAndRootExpr->getReturnSilentVariable();
        }

        return $nodesToAdd;
    }

    private function createAssignExpression(Variable $newVariable, New_ $new): Expression
    {
        $assign = new Assign($newVariable, $new);
        return new Expression($assign);
    }

    private function isNewNodeNeeded(AssignAndRootExpr $assignAndRootExpr): bool
    {
        if ($assignAndRootExpr->isFirstCallFactory()) {
            return true;
        }

        if ($assignAndRootExpr->getRootExpr() === $assignAndRootExpr->getAssignExpr()) {
            return false;
        }

        return $assignAndRootExpr->getRootExpr() instanceof New_;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     * @return Assign[]|\PhpParser\Node\Expr\MethodCall[]
     */
    private function createNonFluentMethodCalls(
        array $chainMethodCalls,
        AssignAndRootExpr $assignAndRootExpr,
        bool $isNewNodeNeeded
    ): array {
        $decoupledMethodCalls = [];

        $lastKey = array_key_last($chainMethodCalls);

        foreach ($chainMethodCalls as $key => $chainMethodCall) {
            // skip first, already handled
            if ($key === $lastKey && $assignAndRootExpr->isFirstCallFactory() && $isNewNodeNeeded) {
                continue;
            }

            $var = $this->resolveMethodCallVar($assignAndRootExpr, $key);

            $chainMethodCall->var = $var;
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

    private function resolveMethodCallVar(AssignAndRootExpr $assignAndRootExpr, int $key): Expr
    {
        if (! $assignAndRootExpr->isFirstCallFactory()) {
            return $assignAndRootExpr->getCallerExpr();
        }

        // very first call
        if ($key !== 0) {
            return $assignAndRootExpr->getCallerExpr();
        }

        return $assignAndRootExpr->getFactoryAssignVariable();
    }
}
