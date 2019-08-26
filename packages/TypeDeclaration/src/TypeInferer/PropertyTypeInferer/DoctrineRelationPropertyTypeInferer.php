<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use PhpParser\Node\Stmt\Property;
use Rector\DeadCode\Doctrine\DoctrineEntityManipulator;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

final class DoctrineRelationPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var string[]
     */
    private const TO_MANY_ANNOTATIONS = [OneToMany::class, ManyToMany::class];

    /**
     * Nullable by default, @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/annotations-reference.html#joincolumn - "JoinColumn" and nullable=true
     * @var string[]
     */
    private const TO_ONE_ANNOTATIONS = [ManyToOne::class, OneToOne::class];

    /**
     * @var string
     */
    private const COLLECTION_TYPE = Collection::class;

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
