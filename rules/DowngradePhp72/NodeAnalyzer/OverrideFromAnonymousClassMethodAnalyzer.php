<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
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
    public function __construct(\Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function matchAncestorClassReflectionOverrideable(\PhpParser\Node\Stmt\ClassLike $classLike, \PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PHPStan\Reflection\ClassReflection
    {
        if (!$this->classAnalyzer->isAnonymousClass($classLike)) {
            return null;
        }
        /** @var Class_ $classLike */
        $interfaces = $classLike->implements;
        foreach ($interfaces as $interface) {
            if (!$interface instanceof \PhpParser\Node\Name\FullyQualified) {
                continue;
            }
            $resolve = $this->resolveClassReflectionWithNotPrivateMethod($interface, $classMethod);
            if ($resolve instanceof \PHPStan\Reflection\ClassReflection) {
                return $resolve;
            }
        }
        /** @var Class_ $classLike */
        if (!$classLike->extends instanceof \PhpParser\Node\Name\FullyQualified) {
            return null;
        }
        return $this->resolveClassReflectionWithNotPrivateMethod($classLike->extends, $classMethod);
    }
    private function resolveClassReflectionWithNotPrivateMethod(\PhpParser\Node\Name\FullyQualified $fullyQualified, \PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PHPStan\Reflection\ClassReflection
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
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $method = $classReflection->getMethod($methodName, $scope);
        if (!$method instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
            return null;
        }
        if ($method->isPrivate()) {
            return null;
        }
        return $classReflection;
    }
}
