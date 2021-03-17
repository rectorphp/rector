<?php

declare(strict_types=1);

namespace Rector\Generics\Reflection;

use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Reflection\ClassReflection;
use Rector\Generics\ValueObject\ChildParentClassReflections;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class GenericClassReflectionAnalyzer
{
    public function resolveChildParent(Class_ $class): ?ChildParentClassReflections
    {
        if ($class->extends === null) {
            return null;
        }

        $scope = $class->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        if (! $this->isGeneric($classReflection)) {
            return null;
        }

        $parentClassReflection = $classReflection->getParentClass();
        if (! $parentClassReflection instanceof ClassReflection) {
            return null;
        }

        if (! $this->isGeneric($parentClassReflection)) {
            return null;
        }

        return new ChildParentClassReflections($classReflection, $parentClassReflection);
    }

    /**
     * Solve isGeneric() ignores extends and similar tags,
     * so it has to be extended with "@extends" and "@implements"
     */
    private function isGeneric(ClassReflection $classReflection): bool
    {
        if ($classReflection->isGeneric()) {
            return true;
        }

        $resolvedPhpDocBlock = $classReflection->getResolvedPhpDoc();
        if (! $resolvedPhpDocBlock instanceof ResolvedPhpDocBlock) {
            return false;
        }

        if ($resolvedPhpDocBlock->getExtendsTags() !== []) {
            return true;
        }

        return $resolvedPhpDocBlock->getImplementsTags() !== [];
    }
}
