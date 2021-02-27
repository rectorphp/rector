<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PHPStan\Reflection\ClassReflection;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;

abstract class AbstractNodeVendorLockResolver
{
    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * @var FamilyRelationsAnalyzer
     */
    protected $familyRelationsAnalyzer;

    /**
     * @required
     */
    public function autowireAbstractNodeVendorLockResolver(
        NodeRepository $nodeRepository,
        NodeNameResolver $nodeNameResolver,
        FamilyRelationsAnalyzer $familyRelationsAnalyzer
    ): void {
        $this->nodeRepository = $nodeRepository;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }

    protected function hasParentClassChildrenClassesOrImplementsInterface(ClassReflection $classReflection): bool
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

    protected function isMethodVendorLockedByInterface(ClassReflection $classReflection, string $methodName): bool
    {
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->hasMethod($methodName)) {
                return true;
            }
        }

        return false;
    }
}
