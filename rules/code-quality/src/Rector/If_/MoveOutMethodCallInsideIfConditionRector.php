<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ThisType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Naming\ExpectedNameResolver;

/**
 * @see \Rector\CodeQuality\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\MoveOutMethodCallInsideIfConditionRectorTest
 */
final class MoveOutMethodCallInsideIfConditionRector extends AbstractRector
{
    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    public function __construct(ExpectedNameResolver $expectedNameResolver)
    {
        $this->expectedNameResolver = $expectedNameResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move out method call inside If condition', [
            new CodeSample(
                <<<'CODE_SAMPLE'
if ($obj->run($arg) === 1) {

}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$objRun = $obj->run($arg);
if ($objRun === 1) {

}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $methodCalls = $this->betterNodeFinder->find($node->cond, function (Node $node): bool {
            return $node instanceof MethodCall;
        });

        $countMethodCalls = count($methodCalls);

        // No method call or Multiple method calls inside if → skip
        if ($countMethodCalls === 0 || $countMethodCalls > 1) {
            return null;
        }

        /** @var MethodCall $methodCall */
        $methodCall = $methodCalls[0];
        $methodCallVar = $methodCall->var;
        $scope = $methodCallVar->getAttribute(Scope::class);
        if ($scope === null) {
            return null;
        }

        $type = $scope->getType($methodCallVar);

        // From PropertyFetch → skip
        if ($type instanceof ThisType) {
            return null;
        }

        // Is Boolean return → skip
        $scope = $methodCall->getAttribute(Scope::class);
        if ($scope === null) {
            return null;
        }

        $type = $scope->getType($methodCall);
        if ($type instanceof BooleanType) {
            return null;
        }

        // No Args → skip
        if ($methodCall->args === []) {
            return null;
        }

        // Inside Method calls args has Method Call again → skip
        if ($this->isInsideMethodCallHasMethodCall($methodCall)) {
            return null;
        }

        return $this->moveOutMethodCall($methodCall, $node);
    }

    private function isInsideMethodCallHasMethodCall(MethodCall $methodCall): bool
    {
        foreach ($methodCall->args as $arg) {
            if ($arg->value instanceof MethodCall) {
                return true;
            }
        }

        return false;
    }

    private function moveOutMethodCall(MethodCall $methodCall, If_ $if): ?If_
    {
        $methodCallVarName = $this->getName($methodCall->var);
        $methodCallIdentifier = $methodCall->name;

        if (! $methodCallIdentifier instanceof Identifier) {
            return null;
        }

        $methodCallName = $methodCallIdentifier->toString();
        if ($methodCallVarName === null || $methodCallName === null) {
            return null;
        }

        $variableName = $this->expectedNameResolver->resolveForCall($methodCall) ?? $methodCallVarName . ucfirst($methodCallName);
        $variable = new Variable($variableName);
        $methodCallAssign = new Assign($variable, $methodCall);

        $this->addNodebeforeNode($methodCallAssign, $if);

        if ($if->cond === $methodCall) {
            $if->cond = $variable;
            return $if;
        }

        $this->traverseNodesWithCallable($if->cond, function (Node $node) use ($variable): ?Variable {
            if ($node instanceof MethodCall) {
                return $variable;
            }

            return null;
        });

        return $if;
    }
}
