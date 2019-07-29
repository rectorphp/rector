<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PropertyTypeInferer;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;

final class DoctrineRelationPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var string[]
     */
    private const RELATION_ANNOTATIONS = ['Doctrine\ORM\Mapping\OneToMany', 'Doctrine\ORM\Mapping\ManyToMany'];

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
        foreach (self::RELATION_ANNOTATIONS as $doctrineRelationAnnotation) {
            if (! $this->docBlockManipulator->hasTag($property, $doctrineRelationAnnotation)) {
                continue;
            }

            $relationTag = $this->docBlockManipulator->getTagByName($property, $doctrineRelationAnnotation);

            $types = [];

            if ($relationTag->value instanceof GenericTagValueNode) {
                $resolveTargetType = $this->resolveTargetEntity($relationTag->value);
                if ($resolveTargetType) {
                    $types[] = $resolveTargetType . '[]';
                }
            }

            $types[] = self::COLLECTION_TYPE;

            return $types;
        }

        return [];
    }

    private function resolveTargetEntity(GenericTagValueNode $genericTagValueNode): ?string
    {
        $match = Strings::match($genericTagValueNode->value, '#targetEntity=\"(?<targetEntity>.*?)\"#');

        return $match['targetEntity'] ?? null;
    }
}
