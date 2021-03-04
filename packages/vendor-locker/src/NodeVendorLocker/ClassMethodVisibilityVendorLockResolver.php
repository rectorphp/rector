<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;

/**
 * @deprecated
 * Merge with @see ClassMethodVisibilityGuard
 */
final class ClassMethodVisibilityVendorLockResolver
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;

    public function __construct(NodeNameResolver $nodeNameResolver, FamilyRelationsAnalyzer $familyRelationsAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }

    /**
     * Checks for:
     * - interface required methods
     * - abstract classes required method
     * - child classes required method
     *
     * Prevents:
     * - changing visibility conflicting with children
     */
    public function isParentLockedMethod(ClassMethod $classMethod): bool
    {
        /** @var Scope $scope */
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);

        /** @var ClassReflection[] $parentClassReflections */
        $parentClassReflections = array_merge($classReflection->getParents(), $classReflection->getInterfaces());

        foreach ($parentClassReflections as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return true;
            }
        }

        return false;
    }

    public function isChildLockedMethod(ClassMethod $classMethod): bool
    {
        /** @var Scope $scope */
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);

        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        foreach ($childrenClassReflections as $childClassReflection) {
            if ($childClassReflection->hasMethod($methodName)) {
                return true;
            }
        }

        return false;
    }
}
