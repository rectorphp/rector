<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
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

    public function __construct(
        private TypeFactory $typeFactory,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private ShortClassExpander $shortClassExpander,
        private ClassAnnotationMatcher $classAnnotationMatcher
    ) {
    }

    public function inferProperty(Property $property): Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $toManyRelationTagValueNode = $phpDocInfo->getByAnnotationClasses([
            'Doctrine\ORM\Mapping\OneToMany',
            'Doctrine\ORM\Mapping\ManyToMany',
        ]);
        if ($toManyRelationTagValueNode !== null) {
            return $this->processToManyRelation($property, $toManyRelationTagValueNode);
        }

        $toOneRelationTagValueNode = $phpDocInfo->getByAnnotationClasses([
            'Doctrine\ORM\Mapping\ManyToOne',
            'Doctrine\ORM\Mapping\OneToOne',
        ]);

        if ($toOneRelationTagValueNode !== null) {
            $joinDoctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\ORM\Mapping\JoinColumn');
            return $this->processToOneRelation(
                $property,
                $toOneRelationTagValueNode,
                $joinDoctrineAnnotationTagValueNode
            );
        }

        return new MixedType();
    }

    public function getPriority(): int
    {
        return 2100;
    }

    private function processToManyRelation(
        Property $property,
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode
    ): Type {
        $types = [];

        $targetEntity = $doctrineAnnotationTagValueNode->getValueWithoutQuotes('targetEntity');
        if ($targetEntity) {
            $entityFullyQualifiedClass = $this->shortClassExpander->resolveFqnTargetEntity($targetEntity, $property);
            $types[] = new ArrayType(new MixedType(), new FullyQualifiedObjectType($entityFullyQualifiedClass));
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

        if (Strings::endsWith($targetEntity, '::class')) {
            $targetEntity = Strings::before($targetEntity, '::class');
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

    private function shouldAddNullType(?DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode): bool
    {
        if ($doctrineAnnotationTagValueNode === null) {
            return true;
        }

        $isNullableValue = $doctrineAnnotationTagValueNode->getValue('nullable');
        return $isNullableValue instanceof ConstExprTrueNode;
    }
}
