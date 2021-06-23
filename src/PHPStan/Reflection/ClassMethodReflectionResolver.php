<?php

declare(strict_types=1);

namespace Rector\Core\PHPStan\Reflection;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassMethodReflectionResolver
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function resolve(ClassMethod $classMethod): ?MethodReflection
    {
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        $methodName = $classMethod->name->toString();
        if (! $classReflection->hasMethod($methodName)) {
            return null;
        }

        return $classReflection->getNativeMethod($methodName);
    }
}
