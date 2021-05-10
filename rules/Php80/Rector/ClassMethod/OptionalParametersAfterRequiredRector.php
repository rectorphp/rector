<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeResolver\ArgumentSorter;
use Rector\Php80\NodeResolver\RequireOptionalParamResolver;
use Rector\Php80\Reflection\MethodReflectionClassMethodResolver;
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
        private MethodReflectionClassMethodResolver $methodReflectionClassMethodResolver
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

        $expectedOrderParams = $this->requireOptionalParamResolver->resolve($classMethod);
        if ($classMethod->params === $expectedOrderParams) {
            return null;
        }

        $classMethod->params = $expectedOrderParams;

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

        $classMethod = $this->methodReflectionClassMethodResolver->resolve(
            $newClassType->getClassName(),
            MethodName::CONSTRUCT
        );
        if (! $classMethod instanceof ClassMethod) {
            return null;
        }

        $expectedOrderedParams = $this->requireOptionalParamResolver->resolve($classMethod);
        if ($expectedOrderedParams === $classMethod->getParams()) {
            return null;
        }

        if (count($new->args) !== count($classMethod->getParams())) {
            return null;
        }

        $newArgs = $this->argumentSorter->sortArgsByExpectedParamOrder($new->args, $expectedOrderedParams);
        if ($new->args === $newArgs) {
            return null;
        }

        $new->args = $newArgs;

        return $new;
    }

    private function refactorMethodCall(MethodCall $methodCall): ?MethodCall
    {
        $classMethod = $this->nodeRepository->findClassMethodByMethodCall($methodCall);
        if (! $classMethod instanceof ClassMethod) {
            return null;
        }

        // because parameters can be already changed
        $originalClassMethod = $classMethod->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (! $originalClassMethod instanceof ClassMethod) {
            return null;
        }

        $expectedOrderedParams = $this->requireOptionalParamResolver->resolve($originalClassMethod);
        if ($expectedOrderedParams === $classMethod->getParams()) {
            return null;
        }

        if (count($methodCall->args) !== count($classMethod->getParams())) {
            return null;
        }

        $newArgs = $this->argumentSorter->sortArgsByExpectedParamOrder($methodCall->args, $expectedOrderedParams);
        if ($methodCall->args === $newArgs) {
            return null;
        }

        $methodCall->args = $newArgs;
        return $methodCall;
    }
}
