<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
final class ParamContravariantDetector
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeCollector\NodeCollector\NodeRepository
     */
    private $nodeRepository;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeRepository = $nodeRepository;
    }
    public function hasParentMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Analyser\Scope $scope) : bool
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if ($classReflection === $ancestorClassReflection) {
                continue;
            }
            $classMethodName = $this->nodeNameResolver->getName($classMethod);
            if ($ancestorClassReflection->hasMethod($classMethodName)) {
                return \true;
            }
        }
        return \false;
    }
    public function hasChildMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Analyser\Scope $classScope) : bool
    {
        $classReflection = $classScope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $classLikes = $this->nodeRepository->findClassesAndInterfacesByType($classReflection->getName());
        foreach ($classLikes as $classLike) {
            $currentClassMethod = $classLike->getMethod($methodName);
            if ($currentClassMethod !== null) {
                return \true;
            }
        }
        return \false;
    }
}
