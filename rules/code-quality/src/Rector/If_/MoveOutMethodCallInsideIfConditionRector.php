<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ThisType;
use Rector\CodeQuality\Naming\MethodCallToVariableNameResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodeQuality\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\MoveOutMethodCallInsideIfConditionRectorTest
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
        $variableName = $this->methodCallToVariableNameResolver->resolveVariableName($methodCall);
        if ($variableName === null || $this->isVariableExists(
            $if,
            $variableName
        ) || $this->isVariableExistsInParentNode(
            $if,
            $variableName
        )) {
            return null;
        }

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

    private function isVariableExists(If_ $if, string $variableName): bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($if, function (Node $node) use ($variableName): bool {
            return $node instanceof Variable && $node->name === $variableName;
        });
    }

    private function isVariableExistsInParentNode(If_ $if, string $variableName): bool
    {
        $parentNode = $if->getAttribute(AttributeKey::PARENT_NODE);
        while ($parentNode) {
            if ($parentNode instanceof ClassMethod || $parentNode instanceof Function_) {
                return $this->isVariableExistsInParams($parentNode->params, $variableName);
            }

            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }

        return false;
    }

    /**
     * @param Param[] $parameters
     */
    private function isVariableExistsInParams(array $parameters, string $variableName): bool
    {
        foreach ($parameters as $param) {
            if ($param->var instanceof Variable && $param->var->name === $variableName) {
                return true;
            }
        }

        return false;
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
}
