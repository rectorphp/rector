<?php

declare(strict_types=1);

namespace Rector\Privatization\VisibilityGuard;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;

final class ChildClassMethodOverrideGuard
{
    /**
     * @var FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;

    public function __construct(FamilyRelationsAnalyzer $familyRelationsAnalyzer)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }

    public function isOverriddenInChildClass(Scope $scope, string $methodName): bool
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }

        $childrenClassReflection = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);

        foreach ($childrenClassReflection as $childClassReflection) {
            $singleChildrenClassReflectionHasMethod = $childClassReflection->hasMethod($methodName);
            if (! $singleChildrenClassReflectionHasMethod) {
                continue;
            }

            $methodReflection = $childClassReflection->getNativeMethod($methodName);
            $methodDeclaringClass = $methodReflection->getDeclaringClass();

            if ($methodDeclaringClass->getName() === $childClassReflection->getName()) {
                return true;
            }
        }

        return false;
    }
}
