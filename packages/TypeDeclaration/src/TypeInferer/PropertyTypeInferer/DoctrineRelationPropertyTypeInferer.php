<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use Rector\DeadCode\Doctrine\DoctrineEntityManipulator;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

final class DoctrineRelationPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var string[]
     */
    private const TO_MANY_ANNOTATIONS = ['Doctrine\ORM\Mapping\OneToMany', 'Doctrine\ORM\Mapping\ManyToMany'];

    /**
     * Nullable by default, @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/annotations-reference.html#joincolumn - "JoinColumn" and nullable=true
     * @var string[]
     */
    private const TO_ONE_ANNOTATIONS = ['Doctrine\ORM\Mapping\ManyToOne', 'Doctrine\ORM\Mapping\OneToOne'];

    /**
     * @var string
     */
    private const COLLECTION_TYPE = 'Doctrine\Common\Collections\Collection';

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var DoctrineEntityManipulator
     */
    private $doctrineEntityManipulator;

    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        DoctrineEntityManipulator $doctrineEntityManipulator
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->doctrineEntityManipulator = $doctrineEntityManipulator;
    }

    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array
    {
        foreach (self::TO_MANY_ANNOTATIONS as $doctrineRelationAnnotation) {
            if (! $this->docBlockManipulator->hasTag($property, $doctrineRelationAnnotation)) {
                continue;
            }

            return $this->processToManyRelation($property);
        }

        foreach (self::TO_ONE_ANNOTATIONS as $doctrineRelationAnnotation) {
            if (! $this->docBlockManipulator->hasTag($property, $doctrineRelationAnnotation)) {
                continue;
            }

            return $this->processToOneRelation($property);
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
    private function processToManyRelation(Property $property): array
    {
        $types = [];

        $relationType = $this->doctrineEntityManipulator->resolveTargetClass($property);
        if ($relationType !== null) {
            $types[] = $relationType . '[]';
        }

        $types[] = self::COLLECTION_TYPE;

        return $types;
    }

    /**
     * @return string[]
     */
    private function processToOneRelation(Property $property): array
    {
        $types = [];

        $relationType = $this->doctrineEntityManipulator->resolveTargetClass($property);
        if ($relationType !== null) {
            $types[] = $relationType;
        }

        if ($this->doctrineEntityManipulator->isNullableRelation($property)) {
            $types[] = 'null';
        }

        return $types;
    }
}
