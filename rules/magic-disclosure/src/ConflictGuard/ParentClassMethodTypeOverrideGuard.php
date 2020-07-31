<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\ConflictGuard;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeCollector\NodeCollector\ParsedFunctionLikeNodeCollector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ParentClassMethodTypeOverrideGuard
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var ParsedFunctionLikeNodeCollector
     */
    private $parsedFunctionLikeNodeCollector;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        ReflectionProvider $reflectionProvider,
        ParsedFunctionLikeNodeCollector $parsedFunctionLikeNodeCollector,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->parsedFunctionLikeNodeCollector = $parsedFunctionLikeNodeCollector;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isReturnTypeChangeAllowed(ClassMethod $classMethod): bool
    {
        // make sure return type is not protected by parent contract
        $parentClassMethodReflection = $this->getParentClassMethod($classMethod);

        // nothign to check
        if ($parentClassMethodReflection === null) {
            return true;
        }

        $parentClassMethod = $this->getClassMethodByMethodReflection($parentClassMethodReflection);

        // if null, we're unable to override → skip it
        return $parentClassMethod !== null;
    }

    private function getClassMethodByMethodReflection(MethodReflection $parentClassMethodReflection): ?ClassMethod
    {
        $methodName = $parentClassMethodReflection->getName();

        /** @var string $className */
        $className = $parentClassMethodReflection->getDeclaringClass()->getName();

        return $this->parsedFunctionLikeNodeCollector->findMethod($className, $methodName);
    }

    private function getParentClassMethod(ClassMethod $classMethod): ?MethodReflection
    {
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        $parentClassName = $classMethod->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return null;
        }

        if (! $this->reflectionProvider->hasClass($parentClassName)) {
            return null;
        }

        $parentClassReflection = $this->reflectionProvider->getClass($parentClassName);

        /** @var ClassReflection[] $parentClassesReflections */
        $parentClassesReflections = array_merge([$parentClassReflection], $parentClassReflection->getParents());

        foreach ($parentClassesReflections as $parentClassesReflection) {
            if (! $parentClassesReflection->hasMethod($methodName)) {
                continue;
            }

            return $parentClassReflection->getNativeMethod($methodName);
        }

        return null;
    }
}
