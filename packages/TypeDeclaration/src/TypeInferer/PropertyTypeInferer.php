<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Stmt\Property;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\Exception\ConflictingPriorityException;
use Rector\TypeDeclaration\ValueObject\IdentifierValueObject;

final class PropertyTypeInferer
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
        $this->sortAndSetPropertyTypeInferers($propertyTypeInferers);
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

    /**
     * @param PropertyTypeInfererInterface[] $propertyTypeInferers
     */
    private function sortAndSetPropertyTypeInferers(array $propertyTypeInferers): void
    {
        foreach ($propertyTypeInferers as $propertyTypeInferer) {
            $this->ensurePriorityIsUnique($propertyTypeInferer);
            $this->propertyTypeInferers[$propertyTypeInferer->getPriority()] = $propertyTypeInferer;
        }

        krsort($this->propertyTypeInferers);
    }

    private function ensurePriorityIsUnique(PropertyTypeInfererInterface $propertyTypeInferer): void
    {
        if (! isset($this->propertyTypeInferers[$propertyTypeInferer->getPriority()])) {
            return;
        }

        $alreadySetPropertyTypeInferer = $this->propertyTypeInferers[$propertyTypeInferer->getPriority()];

        throw new ConflictingPriorityException($propertyTypeInferer, $alreadySetPropertyTypeInferer);
    }
}
