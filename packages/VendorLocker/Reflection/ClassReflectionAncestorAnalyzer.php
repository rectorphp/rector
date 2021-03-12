<?php

declare(strict_types=1);

namespace Rector\VendorLocker\Reflection;

use PHPStan\Reflection\ClassReflection;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;

final class ClassReflectionAncestorAnalyzer
{
    /**
     * @var FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;

    public function __construct(FamilyRelationsAnalyzer $familyRelationsAnalyzer)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }

    public function hasAncestors(ClassReflection $classReflection): bool
    {
        if ($classReflection->isClass()) {
            // has at least interface
            if ($classReflection->getInterfaces() !== []) {
                return true;
            }

            // has at least one parent class
            if ($classReflection->getParents() !== []) {
                return true;
            }

            $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
            return $childrenClassReflections !== [];
        }

        if ($classReflection->isInterface()) {
            return $classReflection->getInterfaces() !== [];
        }

        return false;
    }
}
