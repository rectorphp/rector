<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use Nette\Utils\Strings;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;

final class UnionTypeCommonTypeNarrower
{
    /**
     * @return string[]
     */
    public function narrowToSharedTypes(UnionType $unionType): array
    {
        $availableTypes = [];
        foreach ($unionType->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                return [];
            }

            $availableTypes[] = $this->resolveClassParentClassesAndInterfaces($unionedType);
        }

        /** @var string[] $sharedTypes */
        $sharedTypes = array_intersect(...$availableTypes);

        return array_values($sharedTypes);
    }

    /**
     * @return string[]
     */
    private function resolveClassParentClassesAndInterfaces(TypeWithClassName $typeWithClassName): array
    {
        $parentClasses = class_parents($typeWithClassName->getClassName());
        if ($parentClasses === false) {
            $parentClasses = [];
        }

        $implementedInterfaces = class_implements($typeWithClassName->getClassName());
        if ($implementedInterfaces === false) {
            $implementedInterfaces = [];
        }

        $implementedInterfaces = $this->filterOutNativeInterfaces($implementedInterfaces);

        $classParentClassesAndInterfaces = array_merge($parentClasses, $implementedInterfaces);
        return array_unique($classParentClassesAndInterfaces);
    }

    /**
     * @param class-string[] $interfaces
     * @return class-string[]
     */
    private function filterOutNativeInterfaces(array $interfaces): array
    {
        foreach ($interfaces as $key => $implementedInterface) {
            // remove native interfaces
            if (Strings::contains($implementedInterface, '\\')) {
                continue;
            }

            unset($interfaces[$key]);
        }

        return $interfaces;
    }
}
