<?php

declare (strict_types=1);
namespace Rector\Privatization\VisibilityGuard;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClassMethodVisibilityGuard
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isClassMethodVisibilityGuardedByParent(ClassMethod $classMethod, ClassReflection $classReflection) : bool
    {
        $methodName = $this->nodeNameResolver->getName($classMethod);
        /** @var ClassReflection[] $parentClassReflections */
        $parentClassReflections = \array_merge($classReflection->getParents(), $classReflection->getInterfaces());
        foreach ($parentClassReflections as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        return \false;
    }
    public function isClassMethodVisibilityGuardedByTrait(ClassMethod $classMethod, ClassReflection $classReflection) : bool
    {
        $parentTraitReflections = $this->getLocalAndParentTraitReflections($classReflection);
        $methodName = $this->nodeNameResolver->getName($classMethod);
        foreach ($parentTraitReflections as $parentTraitReflection) {
            if ($parentTraitReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @return ClassReflection[]
     */
    private function getLocalAndParentTraitReflections(ClassReflection $classReflection) : array
    {
        $traitReflections = $classReflection->getTraits();
        foreach ($classReflection->getParents() as $parentClassReflection) {
            foreach ($parentClassReflection->getTraits() as $parentTraitReflection) {
                $traitReflections[] = $parentTraitReflection;
            }
        }
        return $traitReflections;
    }
}
