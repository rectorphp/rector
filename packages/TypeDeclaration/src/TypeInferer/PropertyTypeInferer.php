<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Stmt\Property;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\ValueObject\IdentifierValueObject;

final class PropertyTypeInferer extends AbstractPriorityAwareTypeInferer
{
    /**
     * @var PropertyTypeInfererInterface[]
     */
    private $propertyTypeInferers = [];

    /**
     * @param PropertyTypeInfererInterface[] $propertyTypeInferers
     */
    public function __construct(array $propertyTypeInferers)
    {
        $this->propertyTypeInferers = $this->sortTypeInferersByPriority($propertyTypeInferers);
    }

    /**
     * @return string[]|IdentifierValueObject[]
     */
    public function inferProperty(Property $property): array
    {
        foreach ($this->propertyTypeInferers as $propertyTypeInferers) {
            $types = $propertyTypeInferers->inferProperty($property);
            if ($types !== []) {
                return $types;
            }
        }

        return [];
    }
}
