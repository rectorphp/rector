<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Reflection\MethodReflection;
use Rector\Core\Reflection\ReflectionResolver;

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
}
