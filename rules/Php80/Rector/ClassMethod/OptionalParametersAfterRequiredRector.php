<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PHPStan\Reflection\CallReflectionResolver;
use Rector\Core\PHPStan\Reflection\ClassMethodReflectionResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Php80\NodeResolver\ArgumentSorter;
use Rector\Php80\NodeResolver\RequireOptionalParamResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://php.watch/versions/8.0#deprecate-required-param-after-optional
 *
 * @see \Rector\Tests\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector\OptionalParametersAfterRequiredRectorTest
 */
final class OptionalParametersAfterRequiredRector extends AbstractRector
{
    public function __construct(
        private RequireOptionalParamResolver $requireOptionalParamResolver,
        private ArgumentSorter $argumentSorter,
        private CallReflectionResolver $callReflectionResolver,
        private ClassMethodReflectionResolver $classMethodReflectionResolver,
        private ReflectionResolver $reflectionResolver
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move required parameters after optional ones', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($optional = 1, $required)
    {
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($required, $optional = 1)
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, New_::class, MethodCall::class];
    }

    /**
     * @param ClassMethod|New_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }

        if ($node instanceof New_) {
            return $this->refactorNew($node);
        }

        return $this->refactorMethodCall($node);
    }

    private function refactorClassMethod(ClassMethod $classMethod): ?ClassMethod
    {
        if ($classMethod->params === []) {
            return null;
        }

        $classMethodReflection = $this->classMethodReflectionResolver->resolve($classMethod);
        if (! $classMethodReflection instanceof MethodReflection) {
            return null;
        }

        $parametersAcceptor = $classMethodReflection->getVariants()[0];

        $expectedOrderParameterReflections = $this->requireOptionalParamResolver->resolveFromReflection(
            $classMethodReflection
        );
        if ($parametersAcceptor->getParameters() === $expectedOrderParameterReflections) {
            return null;
        }

        $newParams = $this->argumentSorter->sortArgsByExpectedParamOrder(
            $classMethod->params,
            $expectedOrderParameterReflections
        );
        $classMethod->params = $newParams;

        return $classMethod;
    }

    private function refactorNew(New_ $new): ?New_
    {
        if ($new->args === []) {
            return null;
        }

        $newClassType = $this->nodeTypeResolver->resolve($new->class);
        if (! $newClassType instanceof TypeWithClassName) {
            return null;
        }

        $methodReflection = $this->reflectionResolver->resolveMethodReflection(
            $newClassType->getClassName(),
            MethodName::CONSTRUCT
        );
        if (! $methodReflection instanceof MethodReflection) {
            return null;
        }

        $parametersAcceptor = $methodReflection->getVariants()[0];

        $expectedOrderedParameterReflections = $this->requireOptionalParamResolver->resolveFromReflection(
            $methodReflection
        );
        if ($expectedOrderedParameterReflections === $parametersAcceptor->getParameters()) {
            return null;
        }

        $parametersAcceptor = $methodReflection->getVariants()[0];

        if (count($new->args) !== count($parametersAcceptor->getParameters())) {
            return null;
        }

        $newArgs = $this->argumentSorter->sortArgsByExpectedParamOrder(
            $new->args,
            $expectedOrderedParameterReflections
        );
        if ($new->args === $newArgs) {
            return null;
        }

        $new->args = $newArgs;

        return $new;
    }

    private function refactorMethodCall(MethodCall $methodCall): ?MethodCall
    {
        $callReflection = $this->callReflectionResolver->resolveCall($methodCall);
        if ($callReflection === null) {
            return null;
        }

        $parametersAcceptor = $callReflection->getVariants()[0];

        $expectedOrderedParameterReflections = $this->requireOptionalParamResolver->resolveFromReflection(
            $callReflection
        );
        if ($expectedOrderedParameterReflections === $parametersAcceptor->getParameters()) {
            return null;
        }

        if (count($methodCall->args) !== count($parametersAcceptor->getParameters())) {
            return null;
        }

        $newArgs = $this->argumentSorter->sortArgsByExpectedParamOrder(
            $methodCall->args,
            $expectedOrderedParameterReflections
        );

        if ($methodCall->args === $newArgs) {
            return null;
        }

        $methodCall->args = $newArgs;

        return $methodCall;
    }
}
