<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer;

final class VarDocPropertyTypeInferer
{
    public function __construct(
        private readonly GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer,
        private readonly DefaultValuePropertyTypeInferer $defaultValuePropertyTypeInferer,
        private readonly TypeFactory $typeFactory,
        private readonly DoctrineTypeAnalyzer $doctrineTypeAnalyzer,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly ConstructorAssignDetector $constructorAssignDetector,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly PropertyFetchFinder $propertyFetchFinder,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly PropertyManipulator $propertyManipulator,
        private readonly AssignToPropertyTypeInferer $assignToPropertyTypeInferer
    ) {
    }

    public function inferProperty(Property $property): Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $resolvedType = $phpDocInfo->getVarType();
        if ($resolvedType instanceof VoidType) {
            return new MixedType();
        }

        $class = $this->betterNodeFinder->findParentType($property, Class_::class);
        if (! $class instanceof Class_) {
            return new MixedType();
        }

        // default value type must be added to each resolved type if set
        $propertyDefaultValue = $property->props[0]->default;
        if ($propertyDefaultValue instanceof Expr) {
            $resolvedType = $this->unionTypeWithDefaultExpr($property, $resolvedType);
        } else {
            $resolvedType = $this->makeNullableForAccessedBeforeInitialization($property, $resolvedType, $phpDocInfo);
        }

        $resolvedType = $this->genericClassStringTypeNormalizer->normalize($resolvedType);
        $propertyName = $this->nodeNameResolver->getName($property);
        $assignInferredPropertyType = $this->assignToPropertyTypeInferer->inferPropertyInClassLike(
            $property,
            $propertyName,
            $class
        );

        if ($this->shouldAddNull($resolvedType, $assignInferredPropertyType)) {
            $resolvedType = TypeCombinator::addNull($resolvedType);
        }

        return $resolvedType;
    }

    private function shouldAddNull(Type $resolvedType, ?Type $assignInferredPropertyType): bool
    {
        if (! $assignInferredPropertyType instanceof Type) {
            return false;
        }

        if (! $assignInferredPropertyType instanceof UnionType) {
            return false;
        }

        if (! TypeCombinator::containsNull($assignInferredPropertyType)) {
            return false;
        }

        return ! TypeCombinator::containsNull($resolvedType);
    }

    private function makeNullableForAccessedBeforeInitialization(
        Property $property,
        Type $resolvedType,
        PhpDocInfo $phpDocInfo
    ): Type {
        $types = $resolvedType instanceof UnionType
            ? $resolvedType->getTypes()
            : [$resolvedType];

        foreach ($types as $type) {
            if ($type instanceof NullType) {
                return $resolvedType;
            }
        }

        $classLike = $this->betterNodeFinder->findParentType($property, Class_::class);
        // not has parent Class_? return early
        if (! $classLike instanceof Class_) {
            return $resolvedType;
        }

        // is never accessed, return early
        $propertyName = $this->nodeNameResolver->getName($property);
        $propertyFetches = $this->propertyFetchFinder->findLocalPropertyFetchesByName($classLike, $propertyName);

        if ($propertyFetches === []) {
            return $resolvedType;
        }

        // is filled by __construct() or setUp(), return early
        if ($this->constructorAssignDetector->isPropertyAssigned($classLike, $propertyName)) {
            return $resolvedType;
        }

        // has various Doctrine or JMS annotation, return early
        if ($this->propertyManipulator->isAllowedReadOnly($property, $phpDocInfo)) {
            return $resolvedType;
        }

        return new UnionType([...$types, new NullType()]);
    }

    private function shouldUnionWithDefaultValue(Type $defaultValueType, Type $type): bool
    {
        if ($defaultValueType instanceof MixedType) {
            return false;
        }

        // skip empty array type (mixed[])
        if ($defaultValueType instanceof ArrayType && $defaultValueType->getItemType() instanceof NeverType && ! $type instanceof MixedType) {
            return false;
        }

        if ($type instanceof MixedType) {
            return true;
        }

        return ! $this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($type);
    }

    private function unionWithDefaultValueType(Type $defaultValueType, Type $resolvedType): Type
    {
        $types = [];
        $types[] = $defaultValueType;

        if (! $resolvedType instanceof MixedType) {
            $types[] = $resolvedType;
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    private function unionTypeWithDefaultExpr(Property $property, Type $resolvedType): Type
    {
        $defaultValueType = $this->defaultValuePropertyTypeInferer->inferProperty($property);
        if (! $defaultValueType instanceof Type) {
            return $resolvedType;
        }

        if (! $this->shouldUnionWithDefaultValue($defaultValueType, $resolvedType)) {
            return $resolvedType;
        }

        return $this->unionWithDefaultValueType($defaultValueType, $resolvedType);
    }
}
