<?php

declare(strict_types=1);

namespace Rector\CodeQualityStrict\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ThisType;
use Rector\CodeQuality\Naming\MethodCallToVariableNameResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\MoveOutMethodCallInsideIfConditionRectorTest
 */
final class MoveOutMethodCallInsideIfConditionRector extends AbstractRector
{
    /**
     * @var MethodCallToVariableNameResolver
     */
    private $methodCallToVariableNameResolver;

    public function __construct(MethodCallToVariableNameResolver $methodCallToVariableNameResolver)
    {
        $this->methodCallToVariableNameResolver = $methodCallToVariableNameResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move out method call inside If condition', [
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
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstanceOf($node->cond, MethodCall::class);

        $countMethodCalls = count($methodCalls);

        // No method call or Multiple method calls inside if → skip
        if ($countMethodCalls !== 1) {
            return null;
        }

        $methodCall = $methodCalls[0];

        if ($this->shouldSkipMethodCall($methodCall)) {
            return null;
        }

        return $this->moveOutMethodCall($methodCall, $node);
    }

    private function shouldSkipMethodCall(MethodCall $methodCall): bool
    {
        $methodCallVar = $methodCall->var;

        $scope = $methodCallVar->getAttribute(Scope::class);
        if ($scope === null) {
            return true;
        }

        $type = $scope->getType($methodCallVar);

        // From PropertyFetch → skip
        if ($type instanceof ThisType) {
            return true;
        }

        // Is Boolean return → skip
        $scope = $methodCall->getAttribute(Scope::class);
        if ($scope === null) {
            return true;
        }

        $type = $scope->getType($methodCall);
        if ($type instanceof BooleanType) {
            return true;
        }

        // No Args → skip
        if ($methodCall->args === []) {
            return true;
        }

        // Inside Method calls args has Method Call again → skip
        return $this->isInsideMethodCallHasMethodCall($methodCall);
    }

    private function moveOutMethodCall(MethodCall $methodCall, If_ $if): ?If_
    {
        $hasParentAssign = (bool) $this->betterNodeFinder->findParentType($methodCall, Assign::class);
        if ($hasParentAssign) {
            return null;
        }

        $variableName = $this->methodCallToVariableNameResolver->resolveVariableName($methodCall);
        if ($variableName === null) {
            return null;
        }

        if ($this->isVariableNameAlreadyDefined($if, $variableName)) {
            return null;
        }

        $variable = new Variable($variableName);
        $methodCallAssign = new Assign($variable, $methodCall);

        $this->addNodebeforeNode($methodCallAssign, $if);

        // replace if cond with variable
        if ($if->cond === $methodCall) {
            $if->cond = $variable;
            return $if;
        }

        // replace method call with variable
        $this->traverseNodesWithCallable($if->cond, function (Node $node) use ($variable): ?Variable {
            if ($node instanceof MethodCall) {
                return $variable;
            }

            return null;
        });

        return $if;
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

    private function isVariableNameAlreadyDefined(If_ $if, string $variableName): bool
    {
        /** @var Scope $scope */
        $scope = $if->getAttribute(AttributeKey::SCOPE);

        return $scope->hasVariableType($variableName)
            ->yes();
    }
}
