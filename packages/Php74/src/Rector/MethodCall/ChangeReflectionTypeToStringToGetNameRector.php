<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionFunctionAbstract;
use ReflectionParameter;

/**
 * @see https://www.php.net/manual/en/reflectiontype.tostring.php
 * @see https://www.reddit.com/r/PHP/comments/apikof/whats_the_deal_with_reflectiontype/
 * @see https://3v4l.org/fYeif
 * @see https://3v4l.org/QeM6U
 *
 * @see \Rector\Php74\Tests\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector\ChangeReflectionTypeToStringToGetNameRectorTest
 */
final class ChangeReflectionTypeToStringToGetNameRector extends AbstractRector
{
    /**
     * Possibly extract node decorator with scope breakers (Function_, If_) to respect node flow
     * @var string[][]
     */
    private $callsByVariable = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change string calls on ReflectionType', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function go(ReflectionFunction $reflectionFunction)
    {
        $parameterReflection = $reflectionFunction->getParameters()[0];

        $paramType = (string) $parameterReflection->getType();

        $stringValue = 'hey' . $reflectionFunction->getReturnType();

        // keep
        return $reflectionFunction->getReturnType();
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function go(ReflectionFunction $reflectionFunction)
    {
        $parameterReflection = $reflectionFunction->getParameters()[0];

        $paramType = (string) ($parameterReflection->getType() ? $parameterReflection->getType()->getName() : null);

        $stringValue = 'hey' . ($reflectionFunction->getReturnType() ? $reflectionFunction->getReturnType()->getName() : null);

        // keep
        return $reflectionFunction->getReturnType();
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, String_::class];
    }

    /**
     * @param MethodCall|String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node);
        }

        if ($node instanceof String_) {
            if ($node->expr instanceof MethodCall) {
                return $this->refactorIfHasReturnTypeWasCalled($node->expr);
            }

            if ($node->expr instanceof Variable) {
                if ($this->isObjectType($node->expr, 'ReflectionType')) {
                    return $this->createMethodCall($node->expr, 'getName');
                }
            }
        }

        return null;
    }

    private function refactorMethodCall(MethodCall $methodCall): ?Node
    {
        $this->collectCallByVariable($methodCall);

        if ($this->shouldSkipMethodCall($methodCall)) {
            return null;
        }

        if ($this->isReflectionParameterGetTypeMethodCall($methodCall)) {
            return $this->refactorReflectionParameterGetName($methodCall);
        }

        if ($this->isReflectionFunctionAbstractGetReturnTypeMethodCall($methodCall)) {
            return $this->refactorReflectionFunctionGetReturnType($methodCall);
        }

        return null;
    }

    private function refactorIfHasReturnTypeWasCalled(MethodCall $methodCall): ?Node
    {
        if (! $methodCall->var instanceof Variable) {
            return null;
        }

        $variableName = $this->getName($methodCall->var);

        $callsByVariable = $this->callsByVariable[$variableName] ?? [];

        // we already know it has return type
        if (in_array('hasReturnType', $callsByVariable, true)) {
            return $this->createMethodCall($methodCall, 'getName');
        }

        return null;
    }

    private function collectCallByVariable(Node $node): void
    {
        // bit workaround for now
        if ($node->var instanceof Variable) {
            $variableName = $this->getName($node->var);
            $methodName = $this->getName($node->name);

            if ($variableName && $methodName) {
                $this->callsByVariable[$variableName][] = $methodName;
            }
        }
    }

    private function shouldSkipMethodCall(MethodCall $methodCall): bool
    {
        // just added node → skip it
        if ($methodCall->getAttribute(AttributeKey::SCOPE) === null) {
            return true;
        }

        // is to string retype?
        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof String_) {
            return false;
        }

        if ($parentNode instanceof Concat) {
            return false;
        }

        // probably already converted
        if ($parentNode instanceof Ternary) {
            return true;
        }
        return $parentNode instanceof Return_;
    }

    private function isReflectionParameterGetTypeMethodCall(MethodCall $methodCall): bool
    {
        if (! $this->isObjectType($methodCall->var, ReflectionParameter::class)) {
            return false;
        }

        return $this->isName($methodCall->name, 'getType');
    }

    private function refactorReflectionParameterGetName(MethodCall $methodCall): Ternary
    {
        $getNameMethodCall = $this->createMethodCall($methodCall, 'getName');
        $ternary = new Ternary($methodCall, $getNameMethodCall, $this->createNull());

        // to prevent looping
        $methodCall->setAttribute(AttributeKey::PARENT_NODE, $ternary);

        return $ternary;
    }

    private function isReflectionFunctionAbstractGetReturnTypeMethodCall(MethodCall $methodCall): bool
    {
        if (! $this->isObjectType($methodCall->var, ReflectionFunctionAbstract::class)) {
            return false;
        }

        return $this->isName($methodCall->name, 'getReturnType');
    }

    private function refactorReflectionFunctionGetReturnType(MethodCall $methodCall): Node
    {
        $refactoredMethodCall = $this->refactorIfHasReturnTypeWasCalled($methodCall);
        if ($refactoredMethodCall !== null) {
            return $refactoredMethodCall;
        }

        $getNameMethodCall = $this->createMethodCall($methodCall, 'getName');
        $ternary = new Ternary($methodCall, $getNameMethodCall, $this->createNull());

        // to prevent looping
        $methodCall->setAttribute(AttributeKey::PARENT_NODE, $ternary);

        return $ternary;
    }
}
