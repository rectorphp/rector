<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\PhpDoc\ShortClassExpander;

final class DoctrineRelationPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var string
     */
    private const COLLECTION_TYPE = 'Doctrine\Common\Collections\Collection';

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var ShortClassExpander
     */
    private $shortClassExpander;

    /**
     * @var ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;

    public function __construct(
        TypeFactory $typeFactory,
        PhpDocInfoFactory $phpDocInfoFactory,
        ShortClassExpander $shortClassExpander,
        ClassAnnotationMatcher $classAnnotationMatcher
    ) {
        $this->typeFactory = $typeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->shortClassExpander = $shortClassExpander;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
    }

    public function inferProperty(Property $property): Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $toManyRelationTagValueNode = $phpDocInfo->getByAnnotationClasses([
            'Doctrine\ORM\Mapping\OneToMany',
            'Doctrine\ORM\Mapping\ManyToMany',
        ]);
        if ($toManyRelationTagValueNode !== null) {
            return $this->processToManyRelation($toManyRelationTagValueNode);
        }

        $toOneRelationTagValueNode = $phpDocInfo->getByAnnotationClasses([
            'Doctrine\ORM\Mapping\ManyToOne',
            'Doctrine\ORM\Mapping\OneToOne',
        ]);

        if ($toOneRelationTagValueNode !== null) {
            $joinColumnTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\ORM\Mapping\JoinColumn');
            return $this->processToOneRelation($property, $toOneRelationTagValueNode, $joinColumnTagValueNode);
        }

        return new MixedType();
    }

    public function getPriority(): int
    {
        return 2100;
    }

    private function processToManyRelation(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode): Type
    {
        $types = [];

        $targetEntity = $doctrineAnnotationTagValueNode->getValueWithoutQuotes('targetEntity');
        if ($targetEntity) {
            $types[] = new ArrayType(new MixedType(), new FullyQualifiedObjectType($targetEntity));
        }

        $types[] = new FullyQualifiedObjectType(self::COLLECTION_TYPE);

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    private function processToOneRelation(
        Property $property,
        DoctrineAnnotationTagValueNode $toOneDoctrineAnnotationTagValueNode,
        ?DoctrineAnnotationTagValueNode $joinDoctrineAnnotationTagValueNode
    ): Type {
        $targetEntity = $toOneDoctrineAnnotationTagValueNode->getValueWithoutQuotes('targetEntity');
        if ($targetEntity === null) {
            return new MixedType();
        }

        // resolve to FQN
        $tagFullyQualifiedName = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($targetEntity, $property);

        $types = [];
        $types[] = new FullyQualifiedObjectType($tagFullyQualifiedName);

        if ($this->shouldAddNullType($joinDoctrineAnnotationTagValueNode)) {
            $types[] = new NullType();
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    private function shouldAddNullType(?DoctrineAnnotationTagValueNode $joinColumnTagValueNode): bool
    {
        if ($joinColumnTagValueNode === null) {
            return true;
        }

        $isNullableValue = $joinColumnTagValueNode->getValue('nullable');
        return $isNullableValue === true;
    }
}
