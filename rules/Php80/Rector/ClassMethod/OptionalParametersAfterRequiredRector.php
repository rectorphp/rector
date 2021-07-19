<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\CodingStyle\Reflection\VendorLocationDetector;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    /**
     * @var string
     */
    private const ALREADY_SORTED = 'already_sorted';

    public function __construct(
        private RequireOptionalParamResolver $requireOptionalParamResolver,
        private ArgumentSorter $argumentSorter,
        private ReflectionResolver $reflectionResolver,
        private VendorLocationDetector $vendorLocationDetector
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
        $isAlreadySorted = $node->getAttribute(self::ALREADY_SORTED);
        if ($isAlreadySorted) {
            return null;
        }

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

        $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        $classMethodReflection = $this->reflectionResolver->resolveMethodReflectionFromClassMethod($classMethod);

        if (! $classMethodReflection instanceof MethodReflection) {
            return null;
        }

        $expectedArgOrParamOrder = $this->resolveExpectedArgParamOrderIfDifferent(
            $classMethodReflection,
            $classMethod->params
        );
        if ($expectedArgOrParamOrder === null) {
            return null;
        }

        $newParams = $this->argumentSorter->sortArgsByExpectedParamOrder(
            $classMethod->params,
            $expectedArgOrParamOrder
        );

        $classMethod->params = $newParams;
        $classMethod->setAttribute(self::ALREADY_SORTED, true);

        return $classMethod;
    }

    private function refactorNew(New_ $new): ?New_
    {
        if ($new->args === []) {
            return null;
        }

        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($new);
        if (! $methodReflection instanceof MethodReflection) {
            return null;
        }

        $expectedArgOrParamOrder = $this->resolveExpectedArgParamOrderIfDifferent($methodReflection, $new->args);
        if ($expectedArgOrParamOrder === null) {
            return null;
        }

        $new->args = $this->argumentSorter->sortArgsByExpectedParamOrder($new->args, $expectedArgOrParamOrder);
        $new->setAttribute(self::ALREADY_SORTED, true);

        return $new;
    }

    private function refactorMethodCall(MethodCall $methodCall): ?MethodCall
    {
        $methodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($methodCall);
        if (! $methodReflection instanceof MethodReflection) {
            return null;
        }

        $expectedArgOrParamOrder = $this->resolveExpectedArgParamOrderIfDifferent($methodReflection, $methodCall->args);
        if ($expectedArgOrParamOrder === null) {
            return null;
        }

        $newArgs = $this->argumentSorter->sortArgsByExpectedParamOrder(
            $methodCall->args,
            $expectedArgOrParamOrder
        );

        if ($methodCall->args === $newArgs) {
            return null;
        }

        $methodCall->args = $newArgs;
        $methodCall->setAttribute(self::ALREADY_SORTED, true);

        return $methodCall;
    }

    /**
     * @param array<Node\Arg|Node\Param> $argsOrParams
     * @return int[]|null
     */
    private function resolveExpectedArgParamOrderIfDifferent(
        MethodReflection $methodReflection,
        array $argsOrParams
    ): ?array {
        if ($this->vendorLocationDetector->detectFunctionLikeReflection($methodReflection)) {
            return null;
        }

        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        $expectedParameterReflections = $this->requireOptionalParamResolver->resolveFromReflection(
            $methodReflection
        );

        if (count($argsOrParams) !== count($parametersAcceptor->getParameters())) {
            return null;
        }

        if ($expectedParameterReflections === $parametersAcceptor->getParameters()) {
            return null;
        }

        return array_keys($expectedParameterReflections);
    }
}
