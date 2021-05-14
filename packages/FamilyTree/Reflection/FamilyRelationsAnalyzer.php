<?php

declare (strict_types=1);
namespace Rector\FamilyTree\Reflection;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20210514\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
final class FamilyRelationsAnalyzer
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \RectorPrefix20210514\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->privatesAccessor = $privatesAccessor;
    }
    /**
     * @return ClassReflection[]
     */
    public function getChildrenOfClassReflection(\PHPStan\Reflection\ClassReflection $desiredClassReflection) : array
    {
        /** @var ClassReflection[] $classReflections */
        $classReflections = $this->privatesAccessor->getPrivateProperty($this->reflectionProvider, 'classes');
        $childrenClassReflections = [];
        foreach ($classReflections as $classReflection) {
            if (!$classReflection->isSubclassOf($desiredClassReflection->getName())) {
                continue;
            }
            $childrenClassReflections[] = $classReflection;
        }
        return $childrenClassReflections;
    }
}
