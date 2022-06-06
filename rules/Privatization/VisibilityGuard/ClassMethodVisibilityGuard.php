<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Privatization\VisibilityGuard;

use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class ClassMethodVisibilityGuard
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
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
