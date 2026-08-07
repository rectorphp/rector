<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use RectorPrefix202608\Nette\Utils\Strings;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\Doctrine\CodeQuality\Enum\CollectionMapping;
use Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\Doctrine\NodeAnalyzer\TargetEntityResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class ToOneRelationPropertyTypeResolver
{
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ClassAnnotationMatcher $classAnnotationMatcher;
    /**
     * @readonly
     */
    private AttributeFinder $attributeFinder;
    /**
     * @readonly
     */
    private TargetEntityResolver $targetEntityResolver;
    public function __construct(TypeFactory $typeFactory, PhpDocInfoFactory $phpDocInfoFactory, ClassAnnotationMatcher $classAnnotationMatcher, AttributeFinder $attributeFinder, TargetEntityResolver $targetEntityResolver)
    {
        $this->typeFactory = $typeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->attributeFinder = $attributeFinder;
        $this->targetEntityResolver = $targetEntityResolver;
    }
    public function resolve(Property $property): ?Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClasses(CollectionMapping::TO_ONE_CLASSES);
        if ($doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return $this->processToOneRelation($property, $doctrineAnnotationTagValueNode);
        }
        $expr = $this->attributeFinder->findAttributeByClassesArgByName($property, CollectionMapping::TO_ONE_CLASSES, EntityMappingKey::TARGET_ENTITY);
        if (!$expr instanceof Expr) {
            return null;
        }
        $targetEntityClass = $this->targetEntityResolver->resolveFromExpr($expr);
        if ($targetEntityClass !== null) {
            return $this->resolveNullableObjectType(new FullyQualifiedObjectType($targetEntityClass));
        }
        return null;
    }
    private function processToOneRelation(Property $property, DoctrineAnnotationTagValueNode $toOneDoctrineAnnotationTagValueNode): Type
    {
        $targetEntityArrayItemNode = $toOneDoctrineAnnotationTagValueNode->getValue(EntityMappingKey::TARGET_ENTITY);
        if (!$targetEntityArrayItemNode instanceof ArrayItemNode) {
            return new MixedType();
        }
        $targetEntityClass = $targetEntityArrayItemNode->value;
        if ($targetEntityClass instanceof StringNode) {
            $targetEntityClass = $targetEntityClass->value;
        }
        if (!is_string($targetEntityClass)) {
            return new MixedType();
        }
        if (substr_compare($targetEntityClass, '::class', -strlen('::class')) === 0) {
            $targetEntityClass = Strings::before($targetEntityClass, '::class');
        }
        // resolve to FQN
        $tagFullyQualifiedName = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($targetEntityClass, $property);
        return $this->resolveNullableObjectType(new FullyQualifiedObjectType($tagFullyQualifiedName));
    }
    /**
     * The relation is always nullable, as the entity can be created without the relation being set yet
     */
    private function resolveNullableObjectType(FullyQualifiedObjectType $fullyQualifiedObjectType): Type
    {
        return $this->typeFactory->createMixedPassedOrUnionType([$fullyQualifiedObjectType, new NullType()]);
    }
}
