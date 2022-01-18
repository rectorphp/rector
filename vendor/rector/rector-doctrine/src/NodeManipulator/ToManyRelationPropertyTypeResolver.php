<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\Doctrine\PhpDoc\ShortClassExpander;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class ToManyRelationPropertyTypeResolver
{
    /**
     * @var string
     */
    private const COLLECTION_TYPE = 'Doctrine\\Common\\Collections\\Collection';
    /**
     * @var class-string[]
     */
    private const TO_MANY_ANNOTATION_CLASSES = ['Doctrine\\ORM\\Mapping\\OneToMany', 'Doctrine\\ORM\\Mapping\\ManyToMany'];
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
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Doctrine\PhpDoc\ShortClassExpander $shortClassExpander, \Rector\Doctrine\NodeAnalyzer\AttributeFinder $attributeFinder, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->shortClassExpander = $shortClassExpander;
        $this->attributeFinder = $attributeFinder;
        $this->valueResolver = $valueResolver;
    }
    public function resolve(\PhpParser\Node\Stmt\Property $property) : ?\PHPStan\Type\Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $toManyRelationTagValueNode = $phpDocInfo->getByAnnotationClasses(self::TO_MANY_ANNOTATION_CLASSES);
        if ($toManyRelationTagValueNode !== null) {
            return $this->processToManyRelation($property, $toManyRelationTagValueNode);
        }
        $targetEntity = $this->attributeFinder->findAttributeByClassesArgByName($property, self::TO_MANY_ANNOTATION_CLASSES, 'targetEntity');
        if (!$targetEntity instanceof \PhpParser\Node\Expr) {
            return null;
        }
        return $this->resolveTypeFromTargetEntity($targetEntity, $property);
    }
    /**
     * @return \PHPStan\Type\Type|null
     */
    private function processToManyRelation(\PhpParser\Node\Stmt\Property $property, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
    {
        $targetEntity = $doctrineAnnotationTagValueNode->getValueWithoutQuotes('targetEntity');
        if (!\is_string($targetEntity)) {
            return null;
        }
        return $this->resolveTypeFromTargetEntity($targetEntity, $property);
    }
    /**
     * @param \PhpParser\Node\Expr|string $targetEntity
     */
    private function resolveTypeFromTargetEntity($targetEntity, \PhpParser\Node\Stmt\Property $property) : \PHPStan\Type\Type
    {
        if ($targetEntity instanceof \PhpParser\Node\Expr) {
            $targetEntity = $this->valueResolver->getValue($targetEntity);
        }
        if (!\is_string($targetEntity)) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType(self::COLLECTION_TYPE);
        }
        $entityFullyQualifiedClass = $this->shortClassExpander->resolveFqnTargetEntity($targetEntity, $property);
        $relatedEntityType = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($entityFullyQualifiedClass);
        return new \PHPStan\Type\Generic\GenericObjectType(self::COLLECTION_TYPE, [$relatedEntityType]);
    }
}
