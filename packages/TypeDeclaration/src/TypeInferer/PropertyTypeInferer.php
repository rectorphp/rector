<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Stmt\Property;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

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
        $this->propertyTypeInferers = $propertyTypeInferers;
    }

    /**
     * @property string[]
     */
    public function inferProperty(Property $property): array
    {
        foreach ($this->propertyTypeInferers as $propertyTypeInferers) {
            $types = $propertyTypeInferers->inferFunctionLike($property);
            if ($types !== []) {
                return $types;
            }
        }

        return [];
    }
}
