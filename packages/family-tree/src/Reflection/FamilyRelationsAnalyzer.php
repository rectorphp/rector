<?php

declare(strict_types=1);

namespace Rector\FamilyTree\Reflection;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class FamilyRelationsAnalyzer
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    public function __construct(ReflectionProvider $reflectionProvider, PrivatesAccessor $privatesAccessor)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->privatesAccessor = $privatesAccessor;
    }

    /**
     * @return ClassReflection[]
     */
    public function getChildrenOfClassReflection(ClassReflection $desiredClassReflection): array
    {
        /** @var ClassReflection[] $classReflections */
        $classReflections = $this->privatesAccessor->getPrivateProperty($this->reflectionProvider, 'classes');

        $childrenClassReflections = [];

        foreach ($classReflections as $classReflection) {
            if (! $classReflection->isSubclassOf($desiredClassReflection->getName())) {
                continue;
            }

            $childrenClassReflections[] = $classReflection;
        }

        return $childrenClassReflections;
    }
}
