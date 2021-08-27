<?php

declare (strict_types=1);
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
    /**
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isOverrideParentMethod(\PhpParser\Node\Stmt\ClassLike $classLike, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$this->classAnalyzer->isAnonymousClass($classLike)) {
            return \false;
        }
        /** @var Class_ $classLike */
        if (!$classLike->extends instanceof \PhpParser\Node\Name\FullyQualified) {
            return \false;
        }
        $extendsClass = $classLike->extends->toString();
        if (!$this->reflectionProvider->hasClass($extendsClass)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($extendsClass);
        $methodName = $this->nodeNameResolver->getName($classMethod);
        if (!$classReflection->hasMethod($methodName)) {
            return \false;
        }
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $method = $classReflection->getMethod($methodName, $scope);
        if (!$method instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
            return \false;
        }
        return !$method->isPrivate();
    }
}
