<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ThisType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodeQuality\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\MoveOutMethodCallInsideIfConditionRectorTest
 */
final class MoveOutMethodCallInsideIfConditionRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/LTykey/1
     */
    private const START_ALPHA_REGEX = '#^[a-zA-Z]#';

    /**
     * @var string
     * @see https://regex101.com/r/sYIKpj/1
     */
    private const CONTANT_REGEX = '#(_)([a-z])#';

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
        $variableName = $this->getVariableName($methodCall);
        if ($variableName === null || $this->isVariableExists(
            $if,
            $variableName
        ) || $this->isVariableExistsInParentNode($if, $variableName)) {
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

    private function getVariableName(MethodCall $methodCall): ?string
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

        $variableName = $this->expectedNameResolver->resolveForCall($methodCall);
        if ($methodCall->args === [] && $variableName !== null && $variableName !== $methodCallVarName) {
            return $variableName;
        }

        $arg0 = $methodCall->args[0]->value;
        if ($arg0 instanceof ClassConstFetch && $arg0->name instanceof Identifier) {
            return preg_replace_callback(self::CONTANT_REGEX, function ($matches) {
                return strtoupper($matches[2]);
            }, strtolower($arg0->name->toString()));
        }

        $fallbackVarName = $this->getFallbackVarName($methodCallVarName, $methodCallName);
        if ($arg0 instanceof String_) {
            return $this->getStringVarName($arg0, $methodCallVarName, $fallbackVarName);
        }

        return $fallbackVarName;
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

    private function getFallbackVarName(string $methodCallVarName, string $methodCallName): string
    {
        return $methodCallVarName . ucfirst($methodCallName);
    }

    private function getStringVarName(String_ $string, string $methodCallVarName, string $fallbackVarName): string
    {
        $get = str_ireplace('get', '', $string->value . ucfirst($fallbackVarName));
        $by = str_ireplace('by', '', $get);
        $by = str_replace('-', '', $by);

        if (Strings::match($by, self::START_ALPHA_REGEX) && $by !== $methodCallVarName) {
            return $by;
        }

        return $fallbackVarName;
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
}
