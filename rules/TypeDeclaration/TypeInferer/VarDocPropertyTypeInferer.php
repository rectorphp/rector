<?php

declare (strict_types=1);
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
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer;
final class VarDocPropertyTypeInferer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer
     */
    private $genericClassStringTypeNormalizer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer
     */
    private $defaultValuePropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector
     */
    private $constructorAssignDetector;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder
     */
    private $propertyFetchFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    public function __construct(GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, DefaultValuePropertyTypeInferer $defaultValuePropertyTypeInferer, TypeFactory $typeFactory, DoctrineTypeAnalyzer $doctrineTypeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, ConstructorAssignDetector $constructorAssignDetector, BetterNodeFinder $betterNodeFinder, PropertyFetchFinder $propertyFetchFinder, NodeNameResolver $nodeNameResolver, PropertyManipulator $propertyManipulator, \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer $assignToPropertyTypeInferer, TypeComparator $typeComparator)
    {
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->defaultValuePropertyTypeInferer = $defaultValuePropertyTypeInferer;
        $this->typeFactory = $typeFactory;
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyManipulator = $propertyManipulator;
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->typeComparator = $typeComparator;
    }
    public function inferProperty(Property $property) : Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $varDocType = $phpDocInfo->getVarType();
        if ($varDocType instanceof VoidType) {
            return new MixedType();
        }
        $class = $this->betterNodeFinder->findParentType($property, Class_::class);
        if (!$class instanceof Class_) {
            return new MixedType();
        }
        // default value type must be added to each resolved type if set
        $propertyDefaultValue = $property->props[0]->default;
        if ($propertyDefaultValue instanceof Expr) {
            $resolvedType = $this->unionTypeWithDefaultExpr($property, $varDocType);
        } else {
            $resolvedType = $this->makeNullableForAccessedBeforeInitialization($property, $varDocType, $phpDocInfo);
        }
        $resolvedType = $this->genericClassStringTypeNormalizer->normalize($resolvedType);
        $propertyName = $this->nodeNameResolver->getName($property);
        $assignInferredPropertyType = $this->assignToPropertyTypeInferer->inferPropertyInClassLike($property, $propertyName, $class);
        // if we have no idea what is assigned, the default should be ignored
        if ($assignInferredPropertyType instanceof MixedType) {
            return new MixedType();
        }
        if ($this->shouldAddNull($resolvedType, $assignInferredPropertyType)) {
            $resolvedType = TypeCombinator::addNull($resolvedType);
        }
        if (!$this->typeComparator->areTypesPossiblyIncluded($resolvedType, $assignInferredPropertyType)) {
            return new MixedType();
        }
        return $resolvedType;
    }
    private function shouldAddNull(Type $resolvedType, ?Type $assignInferredPropertyType) : bool
    {
        if (!$assignInferredPropertyType instanceof Type) {
            return \false;
        }
        if (!$assignInferredPropertyType instanceof UnionType) {
            return \false;
        }
        if (!TypeCombinator::containsNull($assignInferredPropertyType)) {
            return \false;
        }
        return !TypeCombinator::containsNull($resolvedType);
    }
    private function makeNullableForAccessedBeforeInitialization(Property $property, Type $resolvedType, PhpDocInfo $phpDocInfo) : Type
    {
        $types = $resolvedType instanceof UnionType ? $resolvedType->getTypes() : [$resolvedType];
        foreach ($types as $type) {
            if ($type instanceof NullType) {
                return $resolvedType;
            }
        }
        $classLike = $this->betterNodeFinder->findParentType($property, Class_::class);
        // not has parent Class_? return early
        if (!$classLike instanceof Class_) {
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
        return new UnionType(\array_merge($types, [new NullType()]));
    }
    private function shouldUnionWithDefaultValue(Type $defaultValueType, Type $type) : bool
    {
        if ($defaultValueType instanceof MixedType) {
            return \false;
        }
        // skip empty array type (mixed[])
        if ($defaultValueType instanceof ArrayType && $defaultValueType->getItemType() instanceof NeverType && !$type instanceof MixedType) {
            return \false;
        }
        if ($type instanceof MixedType) {
            return \true;
        }
        return !$this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($type);
    }
    private function unionWithDefaultValueType(Type $defaultValueType, Type $resolvedType) : Type
    {
        $types = [];
        $types[] = $defaultValueType;
        if (!$resolvedType instanceof MixedType) {
            $types[] = $resolvedType;
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    private function unionTypeWithDefaultExpr(Property $property, Type $varDocType) : Type
    {
        $defaultValueType = $this->defaultValuePropertyTypeInferer->inferProperty($property);
        if (!$defaultValueType instanceof Type) {
            return $varDocType;
        }
        if (!$this->shouldUnionWithDefaultValue($defaultValueType, $varDocType)) {
            return $varDocType;
        }
        return $this->unionWithDefaultValueType($defaultValueType, $varDocType);
    }
}
