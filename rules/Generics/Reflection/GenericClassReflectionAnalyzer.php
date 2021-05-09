<?php

declare (strict_types=1);
namespace Rector\Generics\Reflection;

use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Reflection\ClassReflection;
use Rector\Generics\ValueObject\ChildParentClassReflections;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class GenericClassReflectionAnalyzer
{
    public function resolveChildParent(\PhpParser\Node\Stmt\Class_ $class) : ?\Rector\Generics\ValueObject\ChildParentClassReflections
    {
        if ($class->extends === null) {
            return null;
        }
        $scope = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        if (!$this->isGeneric($classReflection)) {
            return null;
        }
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        if (!$this->isGeneric($parentClassReflection)) {
            return null;
        }
        return new \Rector\Generics\ValueObject\ChildParentClassReflections($classReflection, $parentClassReflection);
    }
    /**
     * Solve isGeneric() ignores extends and similar tags,
     * so it has to be extended with "@extends" and "@implements"
     */
    private function isGeneric(\PHPStan\Reflection\ClassReflection $classReflection) : bool
    {
        if ($classReflection->isGeneric()) {
            return \true;
        }
        $resolvedPhpDocBlock = $classReflection->getResolvedPhpDoc();
        if (!$resolvedPhpDocBlock instanceof \PHPStan\PhpDoc\ResolvedPhpDocBlock) {
            return \false;
        }
        if ($resolvedPhpDocBlock->getExtendsTags() !== []) {
            return \true;
        }
        return $resolvedPhpDocBlock->getImplementsTags() !== [];
    }
}
