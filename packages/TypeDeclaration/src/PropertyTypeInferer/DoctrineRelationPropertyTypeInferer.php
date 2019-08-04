<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PropertyTypeInferer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\NamespaceAnalyzer;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;

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
    private const JOIN_COLUMN_ANNOTATION = 'Doctrine\ORM\Mapping\JoinColumn';

    /**
     * @var string
     */
    private const COLLECTION_TYPE = 'Doctrine\Common\Collections\Collection';

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    public function __construct(DocBlockManipulator $docBlockManipulator, NamespaceAnalyzer $namespaceAnalyzer)
    {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
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

            return $this->processToManyRelation($property, $doctrineRelationAnnotation);
        }

        foreach (self::TO_ONE_ANNOTATIONS as $doctrineRelationAnnotation) {
            if (! $this->docBlockManipulator->hasTag($property, $doctrineRelationAnnotation)) {
                continue;
            }

            return $this->processToOneRelation($property, $doctrineRelationAnnotation);
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
    private function processToManyRelation(Property $property, string $doctrineRelationAnnotation): array
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
    private function processToOneRelation(Property $property, string $doctrineRelationAnnotation): array
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
                if (Strings::contains($resolveTargetType, '\\')) {
                    return $resolveTargetType;
                }

                // is FQN?
                if (! class_exists($resolveTargetType)) {
                    return $this->namespaceAnalyzer->resolveTypeToFullyQualified($resolveTargetType, $property);
                }

                return $resolveTargetType;
            }
        }

        return null;
    }

    private function isNullableOneRelation(Node $node): bool
    {
        if (! $this->docBlockManipulator->hasTag($node, self::JOIN_COLUMN_ANNOTATION)) {
            // @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/annotations-reference.html#joincolumn
            return true;
        }

        $joinColumnTag = $this->docBlockManipulator->getTagByName($node, self::JOIN_COLUMN_ANNOTATION);

        if ($joinColumnTag->value instanceof GenericTagValueNode) {
            return (bool) Strings::match($joinColumnTag->value->value, '#nullable=true#');
        }

        return false;
    }
}
