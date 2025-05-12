<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory;
use Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver;
use Rector\Doctrine\TypeAnalyzer\CollectionVarTagValueNodeResolver;
use Rector\NodeManipulator\AssignManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class SetterCollectionResolver
{
    /**
     * @readonly
     */
    private AssignManipulator $assignManipulator;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private CollectionVarTagValueNodeResolver $collectionVarTagValueNodeResolver;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private CollectionTypeFactory $collectionTypeFactory;
    /**
     * @readonly
     */
    private CollectionTypeResolver $collectionTypeResolver;
    public function __construct(AssignManipulator $assignManipulator, ReflectionResolver $reflectionResolver, NodeNameResolver $nodeNameResolver, CollectionVarTagValueNodeResolver $collectionVarTagValueNodeResolver, StaticTypeMapper $staticTypeMapper, CollectionTypeFactory $collectionTypeFactory, CollectionTypeResolver $collectionTypeResolver)
    {
        $this->assignManipulator = $assignManipulator;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->collectionVarTagValueNodeResolver = $collectionVarTagValueNodeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->collectionTypeFactory = $collectionTypeFactory;
        $this->collectionTypeResolver = $collectionTypeResolver;
    }
    public function resolveAssignedGenericCollectionType(Class_ $class, ClassMethod $classMethod) : ?GenericObjectType
    {
        $propertyFetches = $this->assignManipulator->resolveAssignsToLocalPropertyFetches($classMethod);
        if (\count($propertyFetches) !== 1) {
            return null;
        }
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($propertyFetches[0]);
        if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
            return null;
        }
        $propertyName = (string) $this->nodeNameResolver->getName($propertyFetches[0]);
        $property = $class->getProperty($propertyName);
        if (!$property instanceof Property) {
            return null;
        }
        $varTagValueNode = $this->collectionVarTagValueNodeResolver->resolve($property);
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return null;
        }
        // remove collection union type, so this can be turned into generic type
        $resolvedType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $property);
        if ($resolvedType instanceof UnionType) {
            $nonCollectionTypes = [];
            foreach ($resolvedType->getTypes() as $unionedType) {
                if (!$this->isCollectionType($unionedType)) {
                    continue;
                }
                $nonCollectionTypes[] = $unionedType;
            }
            if (\count($nonCollectionTypes) === 1) {
                $soleType = $nonCollectionTypes[0];
                if ($soleType instanceof ArrayType && $soleType->getItemType() instanceof ObjectType) {
                    return $this->collectionTypeFactory->createType($soleType->getItemType(), $this->collectionTypeResolver->hasIndexBy($property), $property);
                }
            }
        }
        if ($resolvedType instanceof GenericObjectType) {
            return $resolvedType;
        }
        return null;
    }
    private function isCollectionType(Type $type) : bool
    {
        if ($type instanceof ShortenedObjectType && $type->getFullyQualifiedName() === DoctrineClass::COLLECTION) {
            return \true;
        }
        return $type instanceof ObjectType && $type->getClassName() === DoctrineClass::COLLECTION;
    }
}
