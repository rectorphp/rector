<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class OverrideFromAnonymousClassMethodAnalyzer
{
    public function __construct(
        private ClassAnalyzer $classAnalyzer,
        private NodeNameResolver $nodeNameResolver,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function isOverrideParentMethod(ClassLike $classLike, ClassMethod $classMethod): bool
    {
        if (! $this->classAnalyzer->isAnonymousClass($classLike)) {
            return false;
        }

        /** @var Class_ $classLike */
        if (! $classLike->extends instanceof FullyQualified) {
            return false;
        }

        $extendsClass = $classLike->extends->toString();
        if (! $this->reflectionProvider->hasClass($extendsClass)) {
            return false;
        }

        $classReflection = $this->reflectionProvider->getClass($extendsClass);
        $methodName = $this->nodeNameResolver->getName($classMethod);

        if (! $classReflection->hasMethod($methodName)) {
            return false;
        }

        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        $method = $classReflection->getMethod($methodName, $scope);

        if (! $method instanceof PhpMethodReflection) {
            return false;
        }

        return ! $method->isPrivate();
    }
}
