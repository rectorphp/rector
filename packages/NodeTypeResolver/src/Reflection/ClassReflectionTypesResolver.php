<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Reflection;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;

final class ClassReflectionTypesResolver
{
    /**
     * @var Broker
     */
    private $broker;

    public function __construct(Broker $broker)
    {
        $this->broker = $broker;
    }

    /**
     * @return string[]
     */
    public function resolve(ClassReflection $classReflection): array
    {
        $types = [];

        if (! $classReflection->isAnonymous()) {
            $types[] = $classReflection->getName();
        }

        // parent classes
        $types = array_merge($types, $classReflection->getParentClassesNames());

        // interfaces
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            $types[] = $interfaceReflection->getName();
        }

        // traits
        foreach ($classReflection->getTraits() as $traitReflection) {
            $types[] = $traitReflection->getName();
        }

        // to cover traits of parent classes
        foreach ($classReflection->getParentClassesNames() as $parentClassName) {
            $parentClassReflection = $this->broker->getClass($parentClassName);

            foreach ($parentClassReflection->getTraits() as $parentClassTrait) {
                $types[] = $parentClassTrait->getName();
            }
        }

        return $types;
    }
}
