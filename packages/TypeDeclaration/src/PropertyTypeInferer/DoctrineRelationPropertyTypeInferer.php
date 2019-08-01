<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PropertyTypeInferer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;

final class DoctrineRelationPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var string[]
     */
    private const MANY_RELATIONS_ANNOTATIONS = ['Doctrine\ORM\Mapping\OneToMany', 'Doctrine\ORM\Mapping\ManyToMany'];

    /**
     * @var string[]
     */
    private const ONE_RELATION_ANNOTATIONS = ['Doctrine\ORM\Mapping\ManyToOne', 'Doctrine\ORM\Mapping\OneToOne'];

    /**
     * @var string
     */
    private const JOIN_COLUMN_ANNOTATION = 'Doctrine\ORM\Mapping\JoinColumn';

    /**
     * @var string
     */
    private const COLLECTION_TYPE = 'Doctrine\Common\Collections\Collection';

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array
    {
        foreach (self::MANY_RELATIONS_ANNOTATIONS as $doctrineRelationAnnotation) {
            if (! $this->docBlockManipulator->hasTag($property, $doctrineRelationAnnotation)) {
                continue;
            }

            return $this->processManyRelation($property, $doctrineRelationAnnotation);
        }

        foreach (self::ONE_RELATION_ANNOTATIONS as $doctrineRelationAnnotation) {
            if (! $this->docBlockManipulator->hasTag($property, $doctrineRelationAnnotation)) {
                continue;
            }

            return $this->processOneRelation($property, $doctrineRelationAnnotation);
        }

        return [];
    }

    public function getPriority(): int
    {
        return 900;
    }

    /**
     * @return string[]
     */
    private function processManyRelation(Property $property, string $doctrineRelationAnnotation): array
    {
        $types = [];

        $relationType = $this->resolveRelationType($property, $doctrineRelationAnnotation);
        if ($relationType) {
            $types[] = $relationType . '[]';
        }

        $types[] = self::COLLECTION_TYPE;

        return $types;
    }

    /**
     * @return string[]
     */
    private function processOneRelation(Property $property, string $doctrineRelationAnnotation): array
    {
        $types = [];

        $relationType = $this->resolveRelationType($property, $doctrineRelationAnnotation);

        if ($relationType) {
            $types[] = $relationType;
        }

        if ($this->isNullableOneRelation($property)) {
            $types[] = 'null';
        }

        return $types;
    }

    private function resolveTargetEntity(GenericTagValueNode $genericTagValueNode): ?string
    {
        $match = Strings::match($genericTagValueNode->value, '#targetEntity=\"(?<targetEntity>.*?)\"#');

        return $match['targetEntity'] ?? null;
    }

    private function resolveRelationType(Property $property, string $doctrineRelationAnnotation): ?string
    {
        $relationTag = $this->docBlockManipulator->getTagByName($property, $doctrineRelationAnnotation);

        if ($relationTag->value instanceof GenericTagValueNode) {
            $resolveTargetType = $this->resolveTargetEntity($relationTag->value);
            if ($resolveTargetType) {
                return $resolveTargetType;
            }
        }

        return null;
    }

    private function isNullableOneRelation(Node $node): bool
    {
        if (! $this->docBlockManipulator->hasTag($node, self::JOIN_COLUMN_ANNOTATION)) {
            return false;
        }

        $joinColumnTag = $this->docBlockManipulator->getTagByName($node, self::JOIN_COLUMN_ANNOTATION);

        if ($joinColumnTag->value instanceof GenericTagValueNode) {
            return (bool) Strings::match($joinColumnTag->value->value, '#nullable=true#');
        }

        return false;
    }
}
