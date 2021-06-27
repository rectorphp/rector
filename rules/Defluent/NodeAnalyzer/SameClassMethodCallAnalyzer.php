<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface;

final class SameClassMethodCallAnalyzer
{
    public function __construct(
        private ReflectionResolver $reflectionResolver,
    ) {
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     */
    public function haveSingleClass(array $chainMethodCalls): bool
    {
        // are method calls located in the same class?
        $classOfClassMethod = [];
        foreach ($chainMethodCalls as $chainMethodCall) {
            $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($chainMethodCall);

            if ($methodReflection instanceof MethodReflection) {
                $declaringClass = $methodReflection->getDeclaringClass();
                $classOfClassMethod[] = $declaringClass->getName();
            } else {
                $classOfClassMethod[] = null;
            }
        }

        $uniqueClasses = array_unique($classOfClassMethod);
        return count($uniqueClasses) < 2;
    }

    /**
     * @param string[] $calleeUniqueTypes
     */
    public function isCorrectTypeCount(
        array $calleeUniqueTypes,
        FirstCallFactoryAwareInterface $firstCallFactoryAware
    ): bool {
        if ($calleeUniqueTypes === []) {
            return false;
        }

        // in case of factory method, 2 methods are allowed
        if ($firstCallFactoryAware->isFirstCallFactory()) {
            return count($calleeUniqueTypes) === 2;
        }

        return count($calleeUniqueTypes) === 1;
    }
}
