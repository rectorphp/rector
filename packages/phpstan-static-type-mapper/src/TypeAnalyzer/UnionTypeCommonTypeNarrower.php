<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Stmt;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;

final class UnionTypeCommonTypeNarrower
{
    /**
     * Key = the winner
     * Array = the group of types matched
     *
     * @var array<class-string<Node>, array<class-string<Node>>>
     */
    private const PRIORITY_TYPES = [
        BinaryOp::class => [BinaryOp::class, Expr::class],
        Expr::class => [Node::class, Expr::class],
        Stmt::class => [Node::class, Stmt::class],
    ];

    public function narrowToSharedObjectType(UnionType $unionType): ?ObjectType
    {
        $sharedTypes = $this->narrowToSharedTypes($unionType);

        if ($sharedTypes !== []) {
            foreach (self::PRIORITY_TYPES as $winningType => $groupTypes) {
                if (array_intersect($groupTypes, $sharedTypes) === $groupTypes) {
                    return new ObjectType($winningType);
                }
            }

            $firstSharedType = $sharedTypes[0];
            return new ObjectType($firstSharedType);
        }

        return null;
    }

    public function narrowToGenericClassStringType(UnionType $unionType): ?GenericClassStringType
    {
        $availableTypes = [];

        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof GenericClassStringType) {
                if ($unionedType->getGenericType() instanceof TypeWithClassName) {
                    $availableTypes[] = $this->resolveClassParentClassesAndInterfaces($unionedType->getGenericType());
                    continue;
                }
            } else {
                return null;
            }
        }

        return $this->createGenericClassStringType($availableTypes);
    }

    /**
     * @return string[]
     */
    private function narrowToSharedTypes(UnionType $unionType): array
    {
        $availableTypes = [];

        foreach ($unionType->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                return [];
            }

            $availableTypes[] = $this->resolveClassParentClassesAndInterfaces($unionedType);
        }

        return $this->narrowAvailableTypes($availableTypes);
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

        // put earliest interfaces first
        $implementedInterfaces = array_reverse($implementedInterfaces);

        $classParentClassesAndInterfaces = array_merge($implementedInterfaces, $parentClasses);

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

    /**
     * @param string[][] $availableTypes
     * @return string[]
     */
    private function narrowAvailableTypes(array $availableTypes): array
    {
        /** @var string[] $sharedTypes */
        $sharedTypes = array_intersect(...$availableTypes);

        return array_values($sharedTypes);
    }

    /**
     * @param string[][] $availableTypes
     */
    private function createGenericClassStringType(array $availableTypes): ?GenericClassStringType
    {
        $sharedTypes = $this->narrowAvailableTypes($availableTypes);

        if ($sharedTypes !== []) {
            foreach (self::PRIORITY_TYPES as $winningType => $groupTypes) {
                if (array_intersect($groupTypes, $sharedTypes) === $groupTypes) {
                    return new GenericClassStringType(new ObjectType($winningType));
                }
            }

            $firstSharedType = $sharedTypes[0];
            return new GenericClassStringType(new ObjectType($firstSharedType));
        }

        return null;
    }
}
