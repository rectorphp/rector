<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp72\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Reflection\Php\PhpMethodReflection;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\ClassAnalyzer;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
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
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        $method = $classReflection->getMethod($methodName, $scope);
        if (!$method instanceof PhpMethodReflection) {
            return null;
        }
        if ($method->isPrivate()) {
            return null;
        }
        return $classReflection;
    }
}
