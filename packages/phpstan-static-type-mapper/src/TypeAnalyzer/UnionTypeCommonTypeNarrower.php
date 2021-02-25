<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Stmt;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector;

final class UnionTypeCommonTypeNarrower
{
    /**
     * Key = the winner
     * Array = the group of types matched
     *
     * @var array<class-string<Node|\PHPStan\PhpDocParser\Ast\Node>, array<class-string<Node|\PHPStan\PhpDocParser\Ast\Node>>>
     */
    private const PRIORITY_TYPES = [
        BinaryOp::class => [BinaryOp::class, Expr::class],
        Expr::class => [Node::class, Expr::class],
        Stmt::class => [Node::class, Stmt::class],
        'PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode' => [
            'PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode',
            'PHPStan\PhpDocParser\Ast\Node',
        ],
    ];

    /**
     * @var GenericClassStringTypeCorrector
     */
    private $genericClassStringTypeCorrector;

    public function __construct(GenericClassStringTypeCorrector $genericClassStringTypeCorrector)
    {
        $this->genericClassStringTypeCorrector = $genericClassStringTypeCorrector;
    }

    public function narrowToSharedObjectType(UnionType $unionType): ?ObjectType
    {
        $sharedTypes = $this->narrowToSharedTypes($unionType);

        if ($sharedTypes !== []) {
            foreach (self::PRIORITY_TYPES as $winningType => $self::PRIORITY_TYPE) {
                if (array_intersect($self::PRIORITY_TYPE, $sharedTypes) === $self::PRIORITY_TYPE) {
                    return new ObjectType($winningType);
                }
            }

            $firstSharedType = $sharedTypes[0];
            return new ObjectType($firstSharedType);
        }

        return null;
    }

    /**
     * @return GenericClassStringType|UnionType
     */
    public function narrowToGenericClassStringType(UnionType $unionType): Type
    {
        $availableTypes = [];

        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof ConstantStringType) {
                $unionedType = $this->genericClassStringTypeCorrector->correct($unionedType);
            }

            if (! $unionedType instanceof GenericClassStringType) {
                return $unionType;
            }

            if ($unionedType->getGenericType() instanceof TypeWithClassName) {
                $availableTypes[] = $this->resolveClassParentClassesAndInterfaces($unionedType->getGenericType());
            }
        }

        $genericClassStringType = $this->createGenericClassStringType($availableTypes);
        if ($genericClassStringType instanceof GenericClassStringType) {
            return $genericClassStringType;
        }

        return $unionType;
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
        foreach ($interfaces as $key => $interface) {
            // remove native interfaces
            if (Strings::contains($interface, '\\')) {
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
            foreach (self::PRIORITY_TYPES as $winningType => $self::PRIORITY_TYPE) {
                if (array_intersect($self::PRIORITY_TYPE, $sharedTypes) === $self::PRIORITY_TYPE) {
                    return new GenericClassStringType(new ObjectType($winningType));
                }
            }

            $firstSharedType = $sharedTypes[0];
            return new GenericClassStringType(new ObjectType($firstSharedType));
        }

        return null;
    }
}
