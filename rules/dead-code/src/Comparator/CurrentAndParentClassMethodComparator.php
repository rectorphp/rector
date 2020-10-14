<?php

declare(strict_types=1);

namespace Rector\DeadCode\Comparator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

final class CurrentAndParentClassMethodComparator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ValueResolver $valueResolver,
        BetterStandardPrinter $betterStandardPrinter,
        NodeRepository $nodeRepository
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeRepository = $nodeRepository;
    }

    public function isParentCallMatching(ClassMethod $classMethod, ?StaticCall $staticCall): bool
    {
        if ($staticCall === null) {
            return false;
        }

        if (! $this->nodeNameResolver->areNamesEqual($staticCall->name, $classMethod->name)) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($staticCall->class, 'parent')) {
            return false;
        }

        if (! $this->areArgsAndParamsEqual($staticCall->args, $classMethod->params)) {
            return false;
        }

        return ! $this->isParentClassMethodVisibilityOrDefaultOverride($classMethod, $staticCall);
    }

    /**
     * @param Arg[] $args
     * @param Param[] $params
     */
    private function areArgsAndParamsEqual(array $args, array $params): bool
    {
        if (count($args) !== count($params)) {
            return false;
        }

        foreach ($args as $key => $arg) {
            if (! isset($params[$key])) {
                return false;
            }

            $param = $params[$key];

            if (! $this->betterStandardPrinter->areNodesEqual($param->var, $arg->value)) {
                return false;
            }
        }

        return true;
    }

    private function isParentClassMethodVisibilityOrDefaultOverride(
        ClassMethod $classMethod,
        StaticCall $staticCall
    ): bool {
        /** @var string $className */
        $className = $staticCall->getAttribute(AttributeKey::CLASS_NAME);

        $parentClassName = get_parent_class($className);
        if (! $parentClassName) {
            throw new ShouldNotHappenException();
        }

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($staticCall->name);

        $parentClassMethod = $this->nodeRepository->findClassMethod($parentClassName, $methodName);
        if ($parentClassMethod !== null && $parentClassMethod->isProtected() && $classMethod->isPublic()) {
            return true;
        }

        return $this->checkOverrideUsingReflection($classMethod, $parentClassName, $methodName);
    }

    private function checkOverrideUsingReflection(
        ClassMethod $classMethod,
        string $parentClassName,
        string $methodName
    ): bool {
        $parentMethodReflection = $this->getReflectionMethod($parentClassName, $methodName);

        // 3rd party code
        if ($parentMethodReflection !== null) {
            if ($parentMethodReflection->isProtected() && $classMethod->isPublic()) {
                return true;
            }

            if ($parentMethodReflection->isInternal()) {
                //we can't know for certain so we assume its an override
                return true;
            }

            if ($this->areParameterDefaultsDifferent($classMethod, $parentMethodReflection)) {
                return true;
            }
        }

        return false;
    }

    private function getReflectionMethod(string $className, string $methodName): ?ReflectionMethod
    {
        if (! method_exists($className, $methodName)) {
            // internal classes don't have __construct method
            if ($methodName === MethodName::CONSTRUCT && class_exists($className)) {
                return (new ReflectionClass($className))->getConstructor();
            }

            return null;
        }

        return new ReflectionMethod($className, $methodName);
    }

    private function areParameterDefaultsDifferent(
        ClassMethod $classMethod,
        ReflectionMethod $reflectionMethod
    ): bool {
        foreach ($reflectionMethod->getParameters() as $key => $reflectionParameter) {
            if (! isset($classMethod->params[$key])) {
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    continue;
                }
                return true;
            }

            $methodParam = $classMethod->params[$key];

            if ($this->areDefaultValuesDifferent($reflectionParameter, $methodParam)) {
                return true;
            }
        }
        return false;
    }

    private function areDefaultValuesDifferent(ReflectionParameter $reflectionParameter, Param $methodParam): bool
    {
        if ($reflectionParameter->isDefaultValueAvailable() !== isset($methodParam->default)) {
            return true;
        }

        return $reflectionParameter->isDefaultValueAvailable() && $methodParam->default !== null &&
            ! $this->valueResolver->isValue($methodParam->default, $reflectionParameter->getDefaultValue());
    }
}
