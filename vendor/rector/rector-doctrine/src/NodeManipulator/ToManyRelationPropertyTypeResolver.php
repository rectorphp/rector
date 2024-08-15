<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Doctrine\CodeQuality\Enum\CollectionMapping;
use Rector\Doctrine\CodeQuality\Enum\DoctrineClass;
use Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Rector\Doctrine\CodeQuality\Enum\OdmMappingKey;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\Doctrine\PhpDoc\ShortClassExpander;
use Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class ToManyRelationPropertyTypeResolver
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\PhpDoc\ShortClassExpander
     */
    private $shortClassExpander;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory
     */
    private $collectionTypeFactory;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ShortClassExpander $shortClassExpander, AttributeFinder $attributeFinder, ValueResolver $valueResolver, CollectionTypeFactory $collectionTypeFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->shortClassExpander = $shortClassExpander;
        $this->attributeFinder = $attributeFinder;
        $this->valueResolver = $valueResolver;
        $this->collectionTypeFactory = $collectionTypeFactory;
    }
    public function resolve(Property $property) : ?Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClasses(CollectionMapping::TO_MANY_CLASSES);
        if ($doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return $this->processToManyRelation($property, $doctrineAnnotationTagValueNode);
        }
        $expr = $this->attributeFinder->findAttributeByClassesArgByNames($property, CollectionMapping::TO_MANY_CLASSES, [EntityMappingKey::TARGET_ENTITY, OdmMappingKey::TARGET_DOCUMENT]);
        if (!$expr instanceof Expr) {
            return null;
        }
        return $this->resolveTypeFromTargetEntity($expr, $property);
    }
    private function processToManyRelation(Property $property, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : ?\PHPStan\Type\Type
    {
        $targetEntityArrayItemNode = $doctrineAnnotationTagValueNode->getValue(EntityMappingKey::TARGET_ENTITY) ?: $doctrineAnnotationTagValueNode->getValue(OdmMappingKey::TARGET_DOCUMENT);
        if (!$targetEntityArrayItemNode instanceof ArrayItemNode) {
            return null;
        }
        $targetEntityClass = $targetEntityArrayItemNode->value;
        if ($targetEntityClass instanceof StringNode) {
            $targetEntityClass = $targetEntityClass->value;
        }
        if (!\is_string($targetEntityClass)) {
            return null;
        }
        return $this->resolveTypeFromTargetEntity($targetEntityClass, $property);
    }
    /**
     * @param \PhpParser\Node\Expr|string $targetEntity
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    private function resolveTypeFromTargetEntity($targetEntity, $property) : Type
    {
        if ($targetEntity instanceof Expr) {
            $targetEntity = $this->valueResolver->getValue($targetEntity);
        }
        if (!\is_string($targetEntity)) {
            return new FullyQualifiedObjectType(DoctrineClass::COLLECTION);
        }
        $entityFullyQualifiedClass = $this->shortClassExpander->resolveFqnTargetEntity($targetEntity, $property);
        $fullyQualifiedObjectType = new FullyQualifiedObjectType($entityFullyQualifiedClass);
        return $this->collectionTypeFactory->createType($fullyQualifiedObjectType);
    }
}
