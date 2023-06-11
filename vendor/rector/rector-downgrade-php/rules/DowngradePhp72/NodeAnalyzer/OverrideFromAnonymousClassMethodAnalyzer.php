<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class OverrideFromAnonymousClassMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ClassAnalyzer $classAnalyzer, NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function matchAncestorClassReflectionOverrideable(ClassLike $classLike, ClassMethod $classMethod) : ?ClassReflection
    {
        if (!$this->classAnalyzer->isAnonymousClass($classLike)) {
            return null;
        }
        /** @var Class_ $classLike */
        $interfaces = $classLike->implements;
        foreach ($interfaces as $interface) {
            if (!$interface instanceof FullyQualified) {
                continue;
            }
            $resolve = $this->resolveClassReflectionWithNotPrivateMethod($interface, $classMethod);
            if ($resolve instanceof ClassReflection) {
                return $resolve;
            }
        }
        /** @var Class_ $classLike */
        if (!$classLike->extends instanceof FullyQualified) {
            return null;
        }
        return $this->resolveClassReflectionWithNotPrivateMethod($classLike->extends, $classMethod);
    }
    private function resolveClassReflectionWithNotPrivateMethod(FullyQualified $fullyQualified, ClassMethod $classMethod) : ?ClassReflection
    {
        $ancestorClassLike = $fullyQualified->toString();
        if (!$this->reflectionProvider->hasClass($ancestorClassLike)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($ancestorClassLike);
        $methodName = $this->nodeNameResolver->getName($classMethod);
        if (!$classReflection->hasMethod($methodName)) {
            return null;
        }
        /** @var ClassMemberAccessAnswerer $scope */
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        $extendedMethodReflection = $classReflection->getMethod($methodName, $scope);
        if (!$extendedMethodReflection instanceof PhpMethodReflection) {
            return null;
        }
        if ($extendedMethodReflection->isPrivate()) {
            return null;
        }
        return $classReflection;
    }
}
