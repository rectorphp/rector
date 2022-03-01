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
    public function __construct(\Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\DefaultValuePropertyTypeInferer $defaultValuePropertyTypeInferer, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer $doctrineTypeAnalyzer, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector $constructorAssignDetector, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder $propertyFetchFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeManipulator\PropertyManipulator $propertyManipulator)
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
    }
    public function inferProperty(\PhpParser\Node\Stmt\Property $property) : \PHPStan\Type\Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $resolvedType = $phpDocInfo->getVarType();
        if ($resolvedType instanceof \PHPStan\Type\VoidType) {
            return new \PHPStan\Type\MixedType();
        }
        // default value type must be added to each resolved type if set
        $propertyDefaultValue = $property->props[0]->default;
        if ($propertyDefaultValue instanceof \PhpParser\Node\Expr) {
            $resolvedType = $this->unionTypeWithDefaultExpr($property, $resolvedType);
        } else {
            $resolvedType = $this->makeNullableForAccessedBeforeInitialization($property, $resolvedType, $phpDocInfo);
        }
        return $this->genericClassStringTypeNormalizer->normalize($resolvedType);
    }
    private function makeNullableForAccessedBeforeInitialization(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $resolvedType, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : \PHPStan\Type\Type
    {
        $types = $resolvedType instanceof \PHPStan\Type\UnionType ? $resolvedType->getTypes() : [$resolvedType];
        foreach ($types as $type) {
            if ($type instanceof \PHPStan\Type\NullType) {
                return $resolvedType;
            }
        }
        $classLike = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\Class_::class);
        // not has parent Class_? return early
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
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
        return new \PHPStan\Type\UnionType(\array_merge($types, [new \PHPStan\Type\NullType()]));
    }
    private function shouldUnionWithDefaultValue(\PHPStan\Type\Type $defaultValueType, \PHPStan\Type\Type $type) : bool
    {
        if ($defaultValueType instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        // skip empty array type (mixed[])
        if ($defaultValueType instanceof \PHPStan\Type\ArrayType && $defaultValueType->getItemType() instanceof \PHPStan\Type\NeverType && !$type instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        if ($type instanceof \PHPStan\Type\MixedType) {
            return \true;
        }
        return !$this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($type);
    }
    private function unionWithDefaultValueType(\PHPStan\Type\Type $defaultValueType, \PHPStan\Type\Type $resolvedType) : \PHPStan\Type\Type
    {
        $types = [];
        $types[] = $defaultValueType;
        if (!$resolvedType instanceof \PHPStan\Type\MixedType) {
            $types[] = $resolvedType;
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    private function unionTypeWithDefaultExpr(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $resolvedType) : \PHPStan\Type\Type
    {
        $defaultValueType = $this->defaultValuePropertyTypeInferer->inferProperty($property);
        if (!$defaultValueType instanceof \PHPStan\Type\Type) {
            return $resolvedType;
        }
        if (!$this->shouldUnionWithDefaultValue($defaultValueType, $resolvedType)) {
            return $resolvedType;
        }
        return $this->unionWithDefaultValueType($defaultValueType, $resolvedType);
    }
}
