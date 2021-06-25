<?php

declare(strict_types=1);

namespace Rector\Php80\Reflection;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Reflection\ReflectionAstResolver;

final class MethodReflectionClassMethodResolver
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private ReflectionAstResolver $reflectionAstResolver
    ) {
    }

    public function resolve(string $className, string $methodName): ?ClassMethod
    {
        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->isAnonymous()) {
            return null;
        }

        if (! $classReflection->hasMethod($methodName)) {
            return null;
        }

        $constructorClassMethodReflection = $classReflection->getNativeMethod($methodName);
        if (! $constructorClassMethodReflection instanceof PhpMethodReflection) {
            return null;
        }

        return $this->reflectionAstResolver->resolveMethodReflection($constructorClassMethodReflection);
    }
}
